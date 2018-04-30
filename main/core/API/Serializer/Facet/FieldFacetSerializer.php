<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.field_facet")
 * @DI\Tag("claroline.serializer")
 */
class FieldFacetSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ObjectManager */
    private $om;

    private $fieldFacetChoiceRepo;

    /**
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param SerializerProvider $serializer
     * @param ObjectManager      $om
     */
    public function __construct(SerializerProvider $serializer, ObjectManager $om)
    {
        $this->serializer = $serializer;
        $this->om = $om;

        $this->fieldFacetChoiceRepo = $om->getRepository('Claroline\CoreBundle\Entity\Facet\FieldFacetChoice');
    }

    /**
     * Serializes a FieldFacet entity for the JSON api.
     *
     * @param FieldFacet $fieldFacet - the field facet to serialize
     * @param array      $options    - a list of serialization options
     *
     * @return array - the serialized representation of the field facet
     */
    public function serialize(FieldFacet $fieldFacet, array $options = [])
    {
        $serialized = [
            'id' => $fieldFacet->getUuid(),
            'name' => $fieldFacet->getName(),
            'type' => $fieldFacet->getFieldType(),
            'label' => $fieldFacet->getLabel(),
            'required' => $fieldFacet->isRequired(),
            'help' => $fieldFacet->getHelp(),
            'restrictions' => [
                'hidden' => $fieldFacet->isHidden(),
                'isMetadata' => $fieldFacet->getIsMetadata(),
                'locked' => $fieldFacet->isLocked(),
                'lockedEditionOnly' => $fieldFacet->getLockedEditionOnly(),
                'order' => $fieldFacet->getPosition(),
            ],
        ];

        if (!empty($fieldFacet->getOptions())) {
            $serialized['options'] = $fieldFacet->getOptions();
        }

        if ($fieldFacet->getType() === FieldFacet::CHOICE_TYPE) {
            $serialized['options']['choices'] = array_map(function (FieldFacetChoice $choice) {
                return $this->serializer
                    ->get('Claroline\CoreBundle\Entity\Facet\FieldFacetChoice')
                    ->serialize($choice);
            }, $fieldFacet->getFieldFacetChoices()->toArray());
        }

        return $serialized;
    }

    public function deserialize(array $data, FieldFacet $field = null, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $field);
        $this->sipe('label', 'setLabel', $data, $field);
        $this->sipe('type', 'setType', $data, $field);
        $this->sipe('required', 'setRequired', $data, $field);
        $this->sipe('help', 'setHelp', $data, $field);
        $this->sipe('restrictions.hidden', 'setHidden', $data, $field);
        $this->sipe('restrictions.isMetadata', 'setIsMetadata', $data, $field);
        $this->sipe('restrictions.locked', 'setLocked', $data, $field);
        $this->sipe('restrictions.lockedEditionOnly', 'setLockedEditionOnly', $data, $field);
        $this->sipe('restrictions.order', 'setPosition', $data, $field);

        if (isset($data['options'])) {
            $options = $data['options'];

            if (isset($data['options']['choices'])) {
                $choicesData = $data['options']['choices'];
                $oldChoices = $field->getFieldFacetChoicesArray();
                $newChoicesUuids = [];
                $field->emptyFieldFacetChoices();

                foreach ($choicesData as $key => $choiceData) {
                    $newChoicesUuids[] = $choiceData['id'];
                    $choiceData['name'] = $choiceData['value'];
                    $choiceData['position'] = $key + 1;
                    $choice = $this->fieldFacetChoiceRepo->findOneBy(['uuid' => $choiceData['id']]);

                    if (empty($choice)) {
                        $choice = new FieldFacetChoice();
                        $choice->setFieldFacet($field);
                    }
                    $newChoice = $this->serializer
                        ->get('Claroline\CoreBundle\Entity\Facet\FieldFacetChoice')
                        ->deserialize($choiceData, $choice);
                    $this->om->persist($newChoice);

                    $field->addFieldChoice($newChoice);
                }
                $this->om->startFlushSuite();

                /* Removes previous choices that are not used anymore */
                foreach ($oldChoices as $oldChoice) {
                    if (!in_array($oldChoice->getUuid(), $newChoicesUuids)) {
                        $this->om->remove($oldChoice);
                    }
                }
                $this->om->endFlushSuite();
                unset($options['choices']);
            }
            $field->setOptions($options);
        }

        return $field;
    }
}

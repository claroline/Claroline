<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\CoreBundle\API\Serializer\Facet\FieldFacetChoiceSerializer;
use Claroline\CoreBundle\API\Serializer\Facet\FieldFacetSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;
use Claroline\CoreBundle\Manager\FacetManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform.field")
 * @DI\Tag("claroline.serializer")
 */
class FieldSerializer
{
    use SerializerTrait;

    /** @var FieldFacetSerializer */
    private $fieldFacetSerializer;

    /** @var FieldFacetChoiceSerializer */
    private $fieldFacetChoiceSerializer;

    /** @var FacetManager */
    private $facetManager;

    /** @var ObjectManager */
    private $om;

    private $clacoFormRepo;

    /**
     * FieldSerializer constructor.
     *
     * @DI\InjectParams({
     *     "fieldFacetSerializer"       = @DI\Inject("claroline.serializer.field_facet"),
     *     "fieldFacetChoiceSerializer" = @DI\Inject("claroline.serializer.field_facet_choice"),
     *     "facetManager"               = @DI\Inject("claroline.manager.facet_manager"),
     *     "om"                         = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param FieldFacetSerializer       $fieldFacetSerializer
     * @param FieldFacetChoiceSerializer $fieldFacetChoiceSerializer
     * @param FacetManager               $facetManager
     * @param ObjectManager              $om
     */
    public function __construct(
        FieldFacetSerializer $fieldFacetSerializer,
        FieldFacetChoiceSerializer $fieldFacetChoiceSerializer,
        FacetManager $facetManager,
        ObjectManager $om
    ) {
        $this->fieldFacetSerializer = $fieldFacetSerializer;
        $this->fieldFacetChoiceSerializer = $fieldFacetChoiceSerializer;
        $this->facetManager = $facetManager;
        $this->om = $om;

        $this->clacoFormRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\ClacoForm');
    }

    /**
     * Serializes a Field entity for the JSON api.
     *
     * @param Field $field   - the field to serialize
     * @param array $options - a list of serialization options
     *
     * @return array - the serialized representation of the field
     */
    public function serialize(Field $field, array $options = [])
    {
        $serialized = [
            'id' => $field->getUuid(),
            'autoId' => $field->getId(),
            'name' => $field->getName(),
            'label' => $field->getName(),
            'type' => $field->getFieldType(),
            'required' => $field->isRequired(),
            'help' => $field->getHelp(),
            'restrictions' => [
                'isMetadata' => $field->getIsMetadata(),
                'locked' => $field->isLocked(),
                'lockedEditionOnly' => $field->getLockedEditionOnly(),
                'hidden' => $field->isHidden(),
                'order' => $field->getOrder(),
            ],
        ];

        if (count($field->getDetails()) > 0) {
            $serialized['options'] = $field->getDetails();
        }
        if ($field->getType() === FieldFacet::CHOICE_TYPE) {
            $serialized['options']['choices'] = array_map(function (FieldFacetChoice $choice) {
                return $this->fieldFacetChoiceSerializer->serialize($choice);
            }, $field->getFieldFacet()->getFieldFacetChoices()->toArray());
        }
        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'fieldFacet' => $this->fieldFacetSerializer->serialize($field->getFieldFacet()),
            ]);
        } else {
            $serialized = array_merge($serialized, [
                'fieldFacet' => [
                    'id' => $field->getUuid(),
                ],
            ]);
        }

        return $serialized;
    }

    /**
     * @param array $data
     * @param Field $field
     * @param array $options
     *
     * @return Field
     */
    public function deserialize($data, Field $field, array $options = [])
    {
        $this->sipe('id', 'setUuid', $data, $field);
        $this->sipe('label', 'setName', $data, $field);
        $this->sipe('type', 'setType', $data, $field);
        $this->sipe('required', 'setRequired', $data, $field);
        $this->sipe('restrictions.hidden', 'setHidden', $data, $field);
        $this->sipe('restrictions.isMetadata', 'setIsMetadata', $data, $field);
        $this->sipe('restrictions.locked', 'setLocked', $data, $field);
        $this->sipe('restrictions.lockedEditionOnly', 'setLockedEditionOnly', $data, $field);
        $this->sipe('restrictions.order', 'setOrder', $data, $field);
        $this->sipe('help', 'setHelp', $data, $field);
        $this->sipe('options', 'setDetails', $data, $field);

        $fieldFacet = $field->getFieldFacet();

        if (empty($field->getFieldFacet())) {
            $clacoForm = $field->getClacoForm();
            $fieldFacet = new FieldFacet();
            $fieldFacet->setResourceNode($clacoForm->getResourceNode());
        }
        $newFieldFacet = $this->fieldFacetSerializer->deserialize($data, $fieldFacet, $options);
        $this->om->persist($newFieldFacet);
        $field->setFieldFacet($newFieldFacet);

        return $field;
    }
}

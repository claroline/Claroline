<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;

class FieldFacetSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var FieldFacetChoiceSerializer */
    private $ffcSerializer;

    private $fieldFacetChoiceRepo;

    public function __construct(
        ObjectManager $om,
        FieldFacetChoiceSerializer $ffcSerializer
    ) {
        $this->om = $om;
        $this->ffcSerializer = $ffcSerializer;
        $this->fieldFacetChoiceRepo = $om->getRepository(FieldFacetChoice::class);
    }

    public function getName()
    {
        return 'field_facet';
    }

    /**
     * Serializes a FieldFacet entity for the JSON api.
     *
     * @param FieldFacet $fieldFacet - the field facet to serialize
     * @param array      $options    - a list of serialization options
     *
     * @return array - the serialized representation of the field facet
     */
    public function serialize(FieldFacet $fieldFacet, array $options = []): array
    {
        $serialized = [
            'id' => $fieldFacet->getUuid(),
            'name' => $fieldFacet->getName(),
            'type' => $fieldFacet->getType(),
            'label' => $fieldFacet->getLabel(),
            'required' => $fieldFacet->isRequired(),
            'help' => $fieldFacet->getHelp(),
            'display' => [
                'order' => $fieldFacet->getPosition(),
                'condition' => [
                    'field' => $fieldFacet->getConditionField(),
                    'comparator' => $fieldFacet->getConditionComparator(),
                    'value' => $fieldFacet->getConditionValue(),
                ],
            ],
            'restrictions' => [
                'hidden' => $fieldFacet->isHidden(),
                'metadata' => $fieldFacet->isMetadata(),
                'locked' => $fieldFacet->isLocked(),
                'lockedEditionOnly' => $fieldFacet->getLockedEditionOnly(),
            ],
        ];

        if (!empty($fieldFacet->getOptions())) {
            $serialized['options'] = $fieldFacet->getOptions();
        }

        if (in_array($fieldFacet->getType(), [FieldFacet::CHOICE_TYPE, FieldFacet::CASCADE_TYPE])) {
            $serialized['options']['choices'] = array_map(function (FieldFacetChoice $choice) {
                return $this->ffcSerializer->serialize($choice);
            }, $fieldFacet->getRootFieldFacetChoices());
        }

        return $serialized;
    }

    public function deserialize(array $data, FieldFacet $field, array $options = []): FieldFacet
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $field);
        }

        $this->sipe('label', 'setLabel', $data, $field);
        $this->sipe('type', 'setType', $data, $field);
        $this->sipe('required', 'setRequired', $data, $field);
        $this->sipe('help', 'setHelp', $data, $field);

        $this->sipe('display.order', 'setPosition', $data, $field);
        $this->sipe('display.condition.field', 'setConditionField', $data, $field);
        $this->sipe('display.condition.comparator', 'setConditionComparator', $data, $field);
        $this->sipe('display.condition.value', 'setConditionValue', $data, $field);

        $this->sipe('restrictions.hidden', 'setHidden', $data, $field);
        $this->sipe('restrictions.metadata', 'setMetadata', $data, $field);
        $this->sipe('restrictions.locked', 'setLocked', $data, $field);
        $this->sipe('restrictions.lockedEditionOnly', 'setLockedEditionOnly', $data, $field);

        if (isset($data['options'])) {
            if (isset($data['options']['choices'])) {
                $this->deserializeChoices($data['options']['choices'], $field);
                unset($data['options']['choices']);
            }
            $this->sipe('options', 'setOptions', $data, $field);
        }

        return $field;
    }

    private function deserializeChoices(array $choicesData, FieldFacet $field)
    {
        $oldChoices = $field->getRootFieldFacetChoices();
        $newChoicesUuids = [];
        $field->emptyFieldFacetChoices();

        foreach ($choicesData as $key => $choiceData) {
            $isNew = false;
            $newChoicesUuids[] = $choiceData['id'];
            $choiceData['name'] = $choiceData['value'];
            $choiceData['position'] = $key + 1;
            $choice = $this->fieldFacetChoiceRepo->findOneBy(['uuid' => $choiceData['id']]);

            if (empty($choice)) {
                $choice = new FieldFacetChoice();
                $choice->setFieldFacet($field);
                $isNew = true;
            }
            $newChoice = $this->ffcSerializer->deserialize($choiceData, $choice);
            $this->om->persist($newChoice);

            $field->addFieldChoice($newChoice);

            if (isset($choiceData['children'])) {
                $this->deserializeChildrenChoices($choiceData['children'], $newChoice, $field);
            } elseif (!$isNew) {
                $children = $newChoice->getChildren();

                foreach ($children as $child) {
                    $this->om->remove($child);
                }
            }
        }

        /* Removes previous choices that are not used anymore */
        foreach ($oldChoices as $oldChoice) {
            if (!in_array($oldChoice->getUuid(), $newChoicesUuids)) {
                $this->om->remove($oldChoice);
            }
        }
    }

    private function deserializeChildrenChoices(array $choicesData, FieldFacetChoice $parent, FieldFacet $field)
    {
        $oldChoices = $parent->getChildren();
        $newChoicesUuids = [];
        $parent->emptyChildren();

        foreach ($choicesData as $key => $choiceData) {
            $isNew = false;
            $newChoicesUuids[] = $choiceData['id'];
            $choiceData['name'] = $choiceData['value'];
            $choiceData['position'] = $key + 1;
            $choice = $this->fieldFacetChoiceRepo->findOneBy(['uuid' => $choiceData['id']]);

            if (empty($choice)) {
                $choice = new FieldFacetChoice();
                $choice->setUuid($choiceData['id']);
                $choice->setParent($parent);
                $choice->setFieldFacet($field);
                $isNew = true;
            }
            $newChoice = $this->ffcSerializer->deserialize($choiceData, $choice);
            $this->om->persist($newChoice);

            $parent->addChild($newChoice);

            if (isset($choiceData['children'])) {
                $this->deserializeChildrenChoices($choiceData['children'], $newChoice, $field);
            } elseif (!$isNew) {
                $children = $newChoice->getChildren();

                foreach ($children as $child) {
                    $this->om->remove($child);
                }
            }
        }

        /* Removes previous choices that are not used anymore */
        foreach ($oldChoices as $oldChoice) {
            if (!in_array($oldChoice->getUuid(), $newChoicesUuids)) {
                $this->om->remove($oldChoice);
            }
        }
    }
}

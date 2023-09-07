<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use Claroline\CoreBundle\Entity\Facet\FieldFacetChoice;

class FieldFacetSerializer
{
    use SerializerTrait;

    private ObjectManager $om;
    private FieldFacetChoiceSerializer $ffcSerializer;

    public function __construct(
        ObjectManager $om,
        FieldFacetChoiceSerializer $ffcSerializer
    ) {
        $this->om = $om;
        $this->ffcSerializer = $ffcSerializer;
    }

    public function getClass(): string
    {
        return FieldFacet::class;
    }

    public function getName(): string
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
    public function serialize(FieldFacet $fieldFacet, ?array $options = []): array
    {
        $serialized = [
            'id' => $fieldFacet->getUuid(),
            'name' => $fieldFacet->getName(),
            'type' => $fieldFacet->getType(),
            'label' => $fieldFacet->getLabel(),
            'required' => $fieldFacet->isRequired(),
            'help' => $fieldFacet->getHelp(),
            'display' => [
                'order' => $fieldFacet->getOrder(),
                'condition' => [
                    'field' => $fieldFacet->getConditionField(),
                    'comparator' => $fieldFacet->getConditionComparator(),
                    'value' => $fieldFacet->getConditionValue(),
                ],
            ],
            'restrictions' => [
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

    public function deserialize(array $data, FieldFacet $field, ?array $options = []): FieldFacet
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $field);
        } else {
            $field->refreshUuid();
        }

        $this->sipe('label', 'setLabel', $data, $field);
        $this->sipe('type', 'setType', $data, $field);
        $this->sipe('required', 'setRequired', $data, $field);
        $this->sipe('help', 'setHelp', $data, $field);

        $this->sipe('display.order', 'setOrder', $data, $field);
        $this->sipe('display.condition.field', 'setConditionField', $data, $field);
        $this->sipe('display.condition.comparator', 'setConditionComparator', $data, $field);
        $this->sipe('display.condition.value', 'setConditionValue', $data, $field);

        $this->sipe('restrictions.metadata', 'setMetadata', $data, $field);
        $this->sipe('restrictions.locked', 'setLocked', $data, $field);
        $this->sipe('restrictions.lockedEditionOnly', 'setLockedEditionOnly', $data, $field);

        if (isset($data['options'])) {
            if (isset($data['options']['choices'])) {
                $this->deserializeChoices($data['options']['choices'], $field, $options);
                unset($data['options']['choices']);
            }
            $this->sipe('options', 'setOptions', $data, $field);
        }

        return $field;
    }

    private function deserializeChoices(array $choicesData, FieldFacet $field, ?array $options = []): void
    {
        $oldChoices = $field->getRootFieldFacetChoices();
        $newChoicesUuids = [];
        $field->emptyFieldFacetChoices();

        foreach ($choicesData as $key => $choiceData) {
            $isNew = false;
            $choiceData['name'] = $choiceData['value'];
            $choiceData['position'] = $key + 1;

            $choice = null;
            if (!empty($choiceData['id'])) {
                foreach ($oldChoices as $oldChoice) {
                    if ($oldChoice->getUuid() === $choiceData['id']) {
                        $choice = $oldChoice;
                        break;
                    }
                }
            }

            if (empty($choice)) {
                $isNew = true;

                $choice = new FieldFacetChoice();
                $choice->setFieldFacet($field);

                $this->om->persist($choice);
            }

            $this->ffcSerializer->deserialize($choiceData, $choice, $options);
            $newChoicesUuids[] = $choice->getUuid();

            if (isset($choiceData['children'])) {
                $this->deserializeChildrenChoices($choiceData['children'], $choice, $field);
            } elseif (!$isNew) {
                $children = $choice->getChildren();

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

    private function deserializeChildrenChoices(array $choicesData, FieldFacetChoice $parent, FieldFacet $field, ?array $options = []): void
    {
        $oldChoices = $parent->getChildren();
        $newChoicesUuids = [];
        $parent->emptyChildren();

        foreach ($choicesData as $key => $choiceData) {
            $isNew = false;
            $newChoicesUuids[] = $choiceData['id'];
            $choiceData['name'] = $choiceData['value'];
            $choiceData['position'] = $key + 1;

            $choice = null;
            if (!empty($choiceData['id'])) {
                foreach ($oldChoices as $oldChoice) {
                    if ($oldChoice->getUuid() === $choiceData['id']) {
                        $choice = $oldChoice;
                        break;
                    }
                }
            }

            if (empty($choice)) {
                $isNew = true;

                $choice = new FieldFacetChoice();
                $choice->setFieldFacet($field);
                $choice->setParent($parent);
                $parent->addChild($choice);

                $this->om->persist($choice);
            }

            $this->ffcSerializer->deserialize($choiceData, $choice, $options);
            $newChoicesUuids[] = $choice->getUuid();

            if (isset($choiceData['children'])) {
                $this->deserializeChildrenChoices($choiceData['children'], $choice, $field);
            } elseif (!$isNew) {
                $children = $choice->getChildren();

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

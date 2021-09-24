<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\ClacoFormBundle\Entity\FieldChoiceCategory;
use Claroline\CoreBundle\Entity\Facet\FieldFacet;

class FieldChoiceCategorySerializer
{
    use SerializerTrait;

    /** @var FieldSerializer */
    private $fieldSerializer;

    private $categoryRepo;
    private $fieldRepo;

    /**
     * FieldChoiceCategorySerializer constructor.
     */
    public function __construct(FieldSerializer $fieldSerializer, ObjectManager $om)
    {
        $this->fieldSerializer = $fieldSerializer;

        $this->categoryRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\Category');
        $this->fieldRepo = $om->getRepository('Claroline\ClacoFormBundle\Entity\Field');
    }

    public function getName()
    {
        return 'clacoform_field_choice_category';
    }

    /**
     * Serializes a FieldChoiceCategory entity for the JSON api.
     *
     * @return array - the serialized representation of the field
     */
    public function serialize(FieldChoiceCategory $fieldChoiceCategory)
    {
        $serialized = [
            'id' => $fieldChoiceCategory->getUuid(),
            'field' => $this->fieldSerializer->serialize($fieldChoiceCategory->getField(), [Options::SERIALIZE_MINIMAL]),
            'category' => [
                'id' => $fieldChoiceCategory->getCategory()->getUuid(),
            ],
            'value' => $fieldChoiceCategory->getValue(),
        ];

        return $serialized;
    }

    /**
     * @param array $data
     *
     * @return FieldChoiceCategory
     */
    public function deserialize($data, FieldChoiceCategory $fieldChoiceCategory)
    {
        if (isset($data['category']['id'])) {
            $category = $this->categoryRepo->findOneBy(['uuid' => $data['category']['id']]);

            if (!empty($category)) {
                $fieldChoiceCategory->setCategory($category);
            }
        }
        $field = isset($data['field']['id']) ?
            $this->fieldRepo->findOneBy(['uuid' => $data['field']['id']]) :
            null;

        if (!empty($field)) {
            $fieldChoiceCategory->setField($field);

            switch ($field->getType()) {
                case FieldFacet::NUMBER_TYPE:
                    $this->sipe('value', 'setFloatValue', $data, $fieldChoiceCategory);
                    break;
                case FieldFacet::BOOLEAN_TYPE:
                    $this->sipe('value', 'setBoolValue', $data, $fieldChoiceCategory);
                    break;
                case FieldFacet::DATE_TYPE:
                    $date = isset($data['value']) && $data['value'] ? new \DateTime($data['value']) : null;
                    $fieldChoiceCategory->setDateValue($date);
                    break;
                case FieldFacet::CHOICE_TYPE:
                    $options = $field->getOptions();

                    if (isset($options['multiple']) && $options['multiple']) {
                        $this->sipe('value', 'setArrayValue', $data, $fieldChoiceCategory);
                    } else {
                        $this->sipe('value', 'setStringValue', $data, $fieldChoiceCategory);
                    }
                    break;
                case FieldFacet::CASCADE_TYPE:
                    $this->sipe('value', 'setArrayValue', $data, $fieldChoiceCategory);
                    break;
                default:
                    $this->sipe('value', 'setStringValue', $data, $fieldChoiceCategory);
            }
        }

        return $fieldChoiceCategory;
    }
}

<?php

namespace Claroline\ClacoFormBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\CoreBundle\API\Serializer\Facet\FieldFacetValueSerializer;

class FieldValueSerializer
{
    /** @var FieldSerializer */
    private $fieldSerializer;

    /** @var FieldFacetValueSerializer */
    private $fieldFacetValueSerializer;

    /**
     * FieldValueSerializer constructor.
     */
    public function __construct(
        FieldSerializer $fieldSerializer,
        FieldFacetValueSerializer $fieldFacetValueSerializer
    ) {
        $this->fieldSerializer = $fieldSerializer;
        $this->fieldFacetValueSerializer = $fieldFacetValueSerializer;
    }

    public function getName()
    {
        return 'clacoform_field_value';
    }

    /**
     * Serializes a FieldValue entity for the JSON api.
     *
     * @param FieldValue $fieldValue - the field value to serialize
     * @param array      $options    - a list of serialization options
     *
     * @return array - the serialized representation of the field value
     */
    public function serialize(FieldValue $fieldValue, array $options = [])
    {
        $serialized = [
            'id' => $fieldValue->getUuid(),
            'fieldFacetValue' => $this->fieldFacetValueSerializer->serialize($fieldValue->getFieldFacetValue(), [Options::SERIALIZE_MINIMAL]),
        ];

        if (in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'field' => [
                    'id' => $fieldValue->getField()->getUuid(),
                ],
            ]);
        } else {
            $serialized = array_merge($serialized, [
                'field' => $this->fieldSerializer->serialize($fieldValue->getField(), [Options::SERIALIZE_MINIMAL]),
            ]);
        }

        return $serialized;
    }
}

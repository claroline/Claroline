<?php

namespace Claroline\ClacoFormBundle\API\Serializer;

use Claroline\ClacoFormBundle\Entity\FieldValue;
use Claroline\CoreBundle\API\Serializer\Facet\FieldFacetValueSerializer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform.field_value")
 * @DI\Tag("claroline.serializer")
 */
class FieldValueSerializer
{
    const OPTION_MINIMAL = 'minimal';

    /** @var FieldSerializer */
    private $fieldSerializer;

    /** @var FieldFacetValueSerializer */
    private $fieldFacetValueSerializer;

    /**
     * FieldValueSerializer constructor.
     *
     * @DI\InjectParams({
     *     "fieldSerializer"           = @DI\Inject("claroline.serializer.clacoform.field"),
     *     "fieldFacetValueSerializer" = @DI\Inject("claroline.serializer.field_facet_value")
     * })
     *
     * @param FieldSerializer           $fieldSerializer
     * @param FieldFacetValueSerializer $fieldFacetValueSerializer
     */
    public function __construct(
        FieldSerializer $fieldSerializer,
        FieldFacetValueSerializer $fieldFacetValueSerializer
    ) {
        $this->fieldSerializer = $fieldSerializer;
        $this->fieldFacetValueSerializer = $fieldFacetValueSerializer;
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
            'id' => $fieldValue->getId(),
        ];

        if (!in_array(static::OPTION_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'field' => $this->fieldSerializer->serialize($fieldValue->getField(), ['minimal']),
                'fieldFacetValue' => $this->fieldFacetValueSerializer->serialize($fieldValue->getFieldFacetValue(), ['minimal']),
            ]);
        }

        return $serialized;
    }
}

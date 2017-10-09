<?php

namespace Claroline\ClacoFormBundle\API\Serializer;

use Claroline\ClacoFormBundle\Entity\Field;
use Claroline\CoreBundle\API\Serializer\Facet\FieldFacetSerializer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform.field")
 * @DI\Tag("claroline.serializer")
 */
class FieldSerializer
{
    const OPTION_MINIMAL = 'minimal';

    /** @var FieldFacetSerializer */
    private $fieldFacetSerializer;

    /**
     * FieldSerializer constructor.
     *
     * @DI\InjectParams({
     *     "fieldFacetSerializer" = @DI\Inject("claroline.serializer.field_facet")
     * })
     *
     * @param FieldFacetSerializer $fieldFacetSerializer
     */
    public function __construct(FieldFacetSerializer $fieldFacetSerializer)
    {
        $this->fieldFacetSerializer = $fieldFacetSerializer;
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
            'id' => $field->getId(),
            'name' => $field->getName(),
            'type' => $field->getType(),
            'required' => $field->isRequired(),
            'isMetadata' => $field->getIsMetadata(),
            'locked' => $field->isLocked(),
            'lockedEditionOnly' => $field->getLockedEditionOnly(),
            'hidden' => $field->isHidden(),
            'details' => $field->getDetails(),
        ];

        if (!in_array(static::OPTION_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'fieldFacet' => $this->fieldFacetSerializer->serialize($field->getFieldFacet()),
            ]);
        }

        return $serialized;
    }
}

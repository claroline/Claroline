<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\CoreBundle\API\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\Facet\FieldFacetValue;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.field_facet_value")
 * @DI\Tag("claroline.serializer")
 */
class FieldFacetValueSerializer
{
    const OPTION_MINIMAL = 'minimal';

    /** @var FieldFacetSerializer */
    private $fieldFacetSerializer;

    /** @var UserSerializer */
    private $userSerializer;

    /**
     * FieldFacetValueSerializer constructor.
     *
     * @DI\InjectParams({
     *     "fieldFacetSerializer" = @DI\Inject("claroline.serializer.field_facet"),
     *     "userSerializer"       = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param FieldFacetSerializer $fieldFacetSerializer
     * @param UserSerializer       $userSerializer
     */
    public function __construct(
        FieldFacetSerializer $fieldFacetSerializer,
        UserSerializer $userSerializer
    ) {
        $this->fieldFacetSerializer = $fieldFacetSerializer;
        $this->userSerializer = $userSerializer;
    }

    /**
     * Serializes a FieldFacetValue entity for the JSON api.
     *
     * @param FieldFacetValue $fieldFacetValue - the field facet value to serialize
     * @param array           $options         - a list of serialization options
     *
     * @return array - the serialized representation of the field facet value
     */
    public function serialize(FieldFacetValue $fieldFacetValue, array $options = [])
    {
        $serialized = [
            'id' => $fieldFacetValue->getId(),
            'value' => $fieldFacetValue->getValue(),
        ];

        if (!in_array(static::OPTION_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'user' => $fieldFacetValue->getUser() ? $this->userSerializer->serialize($fieldFacetValue->getUser()) : null,
                'fieldFacet' => $this->fieldFacetSerializer->serialize($fieldFacetValue->getFieldFacet()),
            ]);
        }

        return $serialized;
    }
}

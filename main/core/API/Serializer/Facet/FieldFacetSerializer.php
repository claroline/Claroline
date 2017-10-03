<?php

namespace Claroline\CoreBundle\API\Serializer\Facet;

use Claroline\CoreBundle\Entity\Facet\FieldFacet;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.field_facet")
 * @DI\Tag("claroline.serializer")
 */
class FieldFacetSerializer
{
    const OPTION_MINIMAL = 'minimal';

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
            'id' => $fieldFacet->getId(),
            'name' => $fieldFacet->getName(),
            'type' => $fieldFacet->getType(),
            'translationKey' => $fieldFacet->getTypeTranslationKey(),
        ];

        return $serialized;
    }
}

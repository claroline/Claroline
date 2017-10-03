<?php

namespace Claroline\ClacoFormBundle\API\Serializer;

use Claroline\ClacoFormBundle\Entity\Keyword;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.clacoform.keyword")
 * @DI\Tag("claroline.serializer")
 */
class KeywordSerializer
{
    /**
     * Serializes a Keyword entity for the JSON api.
     *
     * @param Keyword $keyword - the keyword to serialize
     * @param array   $options - a list of serialization options
     *
     * @return array - the serialized representation of the keyword
     */
    public function serialize(Keyword $keyword, array $options = [])
    {
        $serialized = [
            'id' => $keyword->getId(),
            'name' => $keyword->getName(),
        ];

        return $serialized;
    }
}

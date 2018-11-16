<?php

namespace Claroline\TagBundle\Serializer;

use Claroline\TagBundle\Entity\TaggedObject;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.tagged_object")
 * @DI\Tag("claroline.serializer")
 */
class TaggedObjectSerializer
{
    public function getClass()
    {
        return TaggedObject::class;
    }

    public function serialize(TaggedObject $taggedObject): array
    {
        return [
            'id' => $taggedObject->getObjectId(),
            'name' => $taggedObject->getObjectName(),
            'type' => $taggedObject->getObjectClass(),
        ];
    }
}

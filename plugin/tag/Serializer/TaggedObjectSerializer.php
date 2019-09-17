<?php

namespace Claroline\TagBundle\Serializer;

use Claroline\TagBundle\Entity\TaggedObject;

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

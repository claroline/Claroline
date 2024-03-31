<?php

namespace Claroline\TagBundle\Serializer;

use Claroline\TagBundle\Entity\TaggedObject;

class TaggedObjectSerializer
{
    public function getClass(): string
    {
        return TaggedObject::class;
    }

    public function getName(): string
    {
        return 'tagged_object';
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

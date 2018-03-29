<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.resource_type")
 * @DI\Tag("claroline.serializer")
 */
class ResourceTypeSerializer
{
    use SerializerTrait;

    /**
     * Serializes a ResourceType entity for the JSON api.
     *
     * @param ResourceType $resourceType - the resource type to serialize
     *
     * @return array - the serialized representation of the resource type
     */
    public function serialize(ResourceType $resourceType)
    {
        return [
            'id' => $resourceType->getId(),
            'name' => $resourceType->getName(),
            'enabled' => $resourceType->isEnabled(),
        ];
    }
}

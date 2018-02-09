<?php

namespace FormaLibre\ReservationBundle\Serializer;

use FormaLibre\ReservationBundle\Entity\ResourceType;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.reservation.resource_type")
 * @DI\Tag("claroline.serializer")
 */
class ResourceTypeSerializer
{
    /**
     * @param ResourceType $resourceType
     *
     * @return array
     */
    public function serialize(ResourceType $resourceType)
    {
        return [
            'id' => $resourceType->getUuid(),
            'name' => $resourceType->getName(),
        ];
    }

    /**
     * Deserializes data into a ResourceType entity.
     *
     * @param \stdClass    $data
     * @param ResourceType $resourceType
     *
     * @return ResourceType
     */
    public function deserialize($data, ResourceType $resourceType = null)
    {
        if (empty($resourceType)) {
            $resourceType = new ResourceType();
        }
        $resourceType->setName($data['name']);

        return $resourceType;
    }
}

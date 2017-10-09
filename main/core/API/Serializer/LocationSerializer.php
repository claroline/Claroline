<?php

namespace Claroline\CoreBundle\API\Serializer;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.location")
 * @DI\Tag("claroline.serializer")
 */
class LocationSerializer extends AbstractSerializer
{
    /**
     * Serializes an Location entity for the JSON api.
     *
     * @param Location $location - the location to serialize
     *
     * @return array - the serialized representation of the location
     */
    public function serialize($location, array $options = [])
    {
        return parent::serialize($location, $options);
    }

    /**
     * Default deserialize method.
     */
    public function deserialize($class, $data, array $options = [])
    {
        return parent::deserialize($class, $data, $options);
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Organization\Location';
    }
}

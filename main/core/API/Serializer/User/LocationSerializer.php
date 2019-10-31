<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Entity\Organization\Location;

class LocationSerializer
{
    use SerializerTrait;

    /**
     * Serialize a Location Entity.
     *
     * @param Location $location
     * @param array    $options
     *
     * @return array
     */
    public function serialize(Location $location, array $options = [])
    {
        return [
            'autoId' => $location->getId(),
            'id' => $location->getUuid(),
            'name' => $location->getName(),
            'meta' => [
                'type' => $location->getType(),
            ],
            'street' => $location->getStreet(),
            'boxNumber' => $location->getBoxNumber(),
            'streetNumber' => $location->getStreetNumber(),
            'zipCode' => $location->getPc(),
            'town' => $location->getTown(),
            'country' => $location->getCountry(),
            'phone' => $location->getPhone(),
            'gps' => [
                'latitude' => $location->getLatitude(),
                'longitude' => $location->getLongitude(),
            ],
        ];
    }

    public function getName()
    {
        return 'location';
    }

    /**
     * Serialize a Location Entity.
     *
     * @param mixed    $data
     * @param Location $location
     * @param array    $options
     *
     * @return Location
     */
    public function deserialize($data, Location $location, array $options = [])
    {
        $this->sipe('name', 'setName', $data, $location);
        $this->sipe('meta.type', 'setType', $data, $location);
        $this->sipe('boxNumber', 'setBoxNumber', $data, $location);
        $this->sipe('street', 'setStreet', $data, $location);
        $this->sipe('streetNumber', 'setStreetNumber', $data, $location);
        $this->sipe('zipCode', 'setPc', $data, $location);
        $this->sipe('town', 'setTown', $data, $location);
        $this->sipe('country', 'setCountry', $data, $location);
        $this->sipe('phone', 'setPhone', $data, $location);

        return $location;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return Location::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/location.json';
    }
}

<?php

namespace Claroline\CoreBundle\API\Serializer\Location;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Location\Location;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LocationSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
    }

    public function getName(): string
    {
        return 'location';
    }

    public function getClass(): string
    {
        return Location::class;
    }

    public function getSchema(): string
    {
        return '#/main/core/location/location.json';
    }

    public function serialize(Location $location, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $location->getUuid(),
                'name' => $location->getName(),
                'thumbnail' => $location->getThumbnail(),

                'address' => [
                    'street1' => $location->getAddressStreet1(),
                    'street2' => $location->getAddressStreet2(),
                    'postalCode' => $location->getAddressPostalCode(),
                    'city' => $location->getAddressCity(),
                    'state' => $location->getAddressState(),
                    'country' => $location->getAddressCountry(),
                ],
            ];
        }

        return [
            'autoId' => $location->getId(),
            'id' => $location->getUuid(),
            'name' => $location->getName(),
            'thumbnail' => $location->getThumbnail(),
            'poster' => $location->getPoster(),
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $location),
                'edit' => $this->authorization->isGranted('EDIT', $location),
                'delete' => $this->authorization->isGranted('DELETE', $location),
            ],
            'meta' => [
                'type' => $location->getType(),
                'description' => $location->getDescription(),
            ],
            'phone' => $location->getPhone(),
            'address' => [
                'street1' => $location->getAddressStreet1(),
                'street2' => $location->getAddressStreet2(),
                'postalCode' => $location->getAddressPostalCode(),
                'city' => $location->getAddressCity(),
                'state' => $location->getAddressState(),
                'country' => $location->getAddressCountry(),
            ],
            'gps' => [
                'latitude' => $location->getLatitude(),
                'longitude' => $location->getLongitude(),
            ],
        ];
    }

    public function deserialize(array $data, Location $location): Location
    {
        $this->sipe('name', 'setName', $data, $location);
        $this->sipe('poster', 'setPoster', $data, $location);
        $this->sipe('thumbnail', 'setThumbnail', $data, $location);
        $this->sipe('meta.type', 'setType', $data, $location);
        $this->sipe('meta.description', 'setDescription', $data, $location);
        $this->sipe('phone', 'setPhone', $data, $location);

        if (isset($data['address'])) {
            $this->sipe('address.street1', 'setAddressStreet1', $data, $location);
            $this->sipe('address.street2', 'setAddressStreet2', $data, $location);
            $this->sipe('address.postalCode', 'setAddressPostalCode', $data, $location);
            $this->sipe('address.city', 'setAddressCity', $data, $location);
            $this->sipe('address.state', 'setAddressState', $data, $location);
            $this->sipe('address.country', 'setAddressCountry', $data, $location);
        }

        return $location;
    }
}

<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Location;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class LocationSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om
    ) {
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
        return '#/main/core/location.json';
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

        $edit = $this->authorization->isGranted('EDIT', $location);

        return [
            'autoId' => $location->getId(),
            'id' => $location->getUuid(),
            'name' => $location->getName(),
            'thumbnail' => $location->getThumbnail(),
            'poster' => $location->getPoster(),
            'permissions' => [
                'open' => $edit || $this->authorization->isGranted('OPEN', $location),
                'edit' => $edit,
                'delete' => $edit || $this->authorization->isGranted('DELETE', $location),
            ],
            'meta' => [
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
        ];
    }

    public function deserialize(array $data, Location $location): Location
    {
        $this->sipe('name', 'setName', $data, $location);
        $this->sipe('poster', 'setPoster', $data, $location);
        $this->sipe('thumbnail', 'setThumbnail', $data, $location);
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

<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Organization\Location;

class LocationSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var PublicFileSerializer */
    private $fileSerializer;

    public function __construct(
        ObjectManager $om,
        PublicFileSerializer $fileSerializer
    ) {
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
    }

    public function getName()
    {
        return 'location';
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

    public function serialize(Location $location, array $options = []): array
    {
        return [
            'autoId' => $location->getId(),
            'id' => $location->getUuid(),
            'name' => $location->getName(),
            'poster' => $this->serializePoster($location),
            'thumbnail' => $this->serializeThumbnail($location),
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

    public function deserialize(array $data, Location $location, array $options = []): Location
    {
        $this->sipe('name', 'setName', $data, $location);
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

        if (isset($data['poster'])) {
            $location->setPoster($data['poster']['url'] ?? null);
        }

        if (isset($data['thumbnail'])) {
            $location->setThumbnail($data['thumbnail']['url'] ?? null);
        }

        return $location;
    }

    private function serializePoster(Location $location)
    {
        if (!empty($location->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $location->getPoster()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    private function serializeThumbnail(Location $location)
    {
        if (!empty($location->getThumbnail())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $location->getThumbnail()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }
}

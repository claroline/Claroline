<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\API\Serializer\Location;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Entity\Location\Room;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RoomSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var LocationSerializer */
    private $locationSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        PublicFileSerializer $fileSerializer,
        LocationSerializer $locationSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
        $this->locationSerializer = $locationSerializer;
    }

    public function getSchema()
    {
        return '#/main/core/location/room.json';
    }

    public function serialize(Room $room, array $options = []): array
    {
        return [
            'id' => $room->getUuid(),
            'code' => $room->getCode(),
            'name' => $room->getName(),
            'description' => $room->getDescription(),
            'capacity' => $room->getCapacity(),
            'poster' => $this->serializePoster($room),
            'thumbnail' => $this->serializeThumbnail($room),
            'location' => $room->getLocation() ? $this->locationSerializer->serialize($room->getLocation(), [Options::SERIALIZE_MINIMAL]) : null,
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $room),
                'edit' => $this->authorization->isGranted('EDIT', $room),
                'delete' => $this->authorization->isGranted('DELETE', $room),
            ],
        ];
    }

    public function deserialize(array $data, Room $room, array $options): Room
    {
        $this->sipe('id', 'setUuid', $data, $room);
        $this->sipe('code', 'setCode', $data, $room);
        $this->sipe('name', 'setName', $data, $room);
        $this->sipe('description', 'setDescription', $data, $room);
        $this->sipe('capacity', 'setCapacity', $data, $room);

        if (isset($data['location'])) {
            $location = null;
            if (isset($data['location']['id'])) {
                $location = $this->om->getRepository(Location::class)->findOneBy(['uuid' => $data['location']['id']]);
            }

            $room->setLocation($location);
        }

        if (isset($data['poster'])) {
            $room->setPoster($data['poster']['url'] ?? null);
        }

        if (isset($data['thumbnail'])) {
            $room->setThumbnail($data['thumbnail']['url'] ?? null);
        }

        return $room;
    }

    private function serializePoster(Room $room)
    {
        if (!empty($room->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $room->getPoster()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    private function serializeThumbnail(Room $room)
    {
        if (!empty($room->getThumbnail())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $room->getThumbnail()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }
}

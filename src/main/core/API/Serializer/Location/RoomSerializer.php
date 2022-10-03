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
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
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
    /** @var LocationSerializer */
    private $locationSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        LocationSerializer $locationSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->locationSerializer = $locationSerializer;
    }

    public function getSchema()
    {
        return '#/main/core/location/room.json';
    }

    public function serialize(Room $room, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $room->getUuid(),
                'code' => $room->getCode(),
                'name' => $room->getName(),
                'thumbnail' => $room->getThumbnail(),
            ];
        }

        return [
            'autoId' => $room->getId(),
            'id' => $room->getUuid(),
            'code' => $room->getCode(),
            'name' => $room->getName(),
            'thumbnail' => $room->getThumbnail(),
            'poster' => $room->getPoster(),
            'description' => $room->getDescription(),
            'capacity' => $room->getCapacity(),
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
        $this->sipe('poster', 'setPoster', $data, $room);
        $this->sipe('thumbnail', 'setThumbnail', $data, $room);

        if (isset($data['location'])) {
            $location = null;
            if (isset($data['location']['id'])) {
                $location = $this->om->getRepository(Location::class)->findOneBy(['uuid' => $data['location']['id']]);
            }

            $room->setLocation($location);
        }

        return $room;
    }
}

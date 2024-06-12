<?php

namespace Claroline\CoreBundle\API\Serializer\Planning;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\LocationSerializer;
use Claroline\CoreBundle\Entity\Location;
use Claroline\CoreBundle\Entity\Planning\PlannedObject;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class PlannedObjectSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly UserSerializer $userSerializer,
        private readonly LocationSerializer $locationSerializer
    ) {
    }

    public function serialize(PlannedObject $plannedObject, array $options = []): array
    {
        $serialized = [
            'id' => $plannedObject->getUuid(),
            'name' => $plannedObject->getName(),
            'start' => $plannedObject->getStartDate() ? DateNormalizer::normalize($plannedObject->getStartDate()) : null,
            'end' => $plannedObject->getEndDate() ? DateNormalizer::normalize($plannedObject->getEndDate()) : null,
            'thumbnail' => $plannedObject->getThumbnail(),
            'description' => $plannedObject->getDescription(),
            'meta' => [
                'type' => $plannedObject->getType(),
                'creator' => $plannedObject->getCreator() ? $this->userSerializer->serialize($plannedObject->getCreator(), [Options::SERIALIZE_MINIMAL]) : null,
                'created' => DateNormalizer::normalize($plannedObject->getCreatedAt()),
                'updated' => DateNormalizer::normalize($plannedObject->getUpdatedAt()),
            ],
            'locationUrl' => $plannedObject->getLocationUrl(),
            'location' => $plannedObject->getLocation() ? $this->locationSerializer->serialize($plannedObject->getLocation(), [Options::SERIALIZE_MINIMAL]) : null,
            'display' => [
                'color' => $plannedObject->getColor(),
            ],
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'poster' => $plannedObject->getPoster(),
            ]);
        }

        return $serialized;
    }

    public function deserialize(array $data, PlannedObject $planned): PlannedObject
    {
        $this->sipe('id', 'setUuid', $data, $planned);
        $this->sipe('name', 'setName', $data, $planned);
        $this->sipe('display.color', 'setColor', $data, $planned);
        $this->sipe('description', 'setDescription', $data, $planned);
        $this->sipe('locationUrl', 'setLocationUrl', $data, $planned);
        $this->sipe('poster', 'setPoster', $data, $planned);
        $this->sipe('thumbnail', 'setThumbnail', $data, $planned);

        if (isset($data['meta'])) {
            if (isset($data['meta']['creator'])) {
                /** @var User $creator */
                $creator = $this->om->getObject($data['meta']['creator'], User::class);
                $planned->setCreator($creator);
            }
        }

        if (array_key_exists('location', $data)) {
            $location = null;
            if (isset($data['location'], $data['location']['id'])) {
                /** @var Location $location */
                $location = $this->om->getObject($data['location'], Location::class);
            }

            $planned->setLocation($location);
        }

        if (isset($data['start'])) {
            $planned->setStartDate(DateNormalizer::denormalize($data['start']));
        }

        if (isset($data['end'])) {
            $planned->setEndDate(DateNormalizer::denormalize($data['end']));
        }

        return $planned;
    }
}

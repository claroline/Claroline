<?php

namespace Claroline\AnnouncementBundle\Serializer;

use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;

class AnnouncementAggregateSerializer
{
    use SerializerTrait;

    public function getName(): string
    {
        return 'announcement_aggregate';
    }

    public function getClass(): string
    {
        return AnnouncementAggregate::class;
    }

    public function serialize(AnnouncementAggregate $announcements, ?array $options = []): array
    {
        return [
            'id' => $announcements->getUuid(),
        ];
    }

    public function deserialize(array $data, AnnouncementAggregate $aggregate, ?array $options = []): AnnouncementAggregate
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $aggregate);
        } else {
            $aggregate->refreshUuid();
        }

        return $aggregate;
    }
}

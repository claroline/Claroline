<?php

namespace Claroline\NotificationBundle\Serializer;

use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\NotificationBundle\Entity\Notification;

class NotificationSerializer
{
    public function getName(): string
    {
        return 'notification';
    }

    public function getClass(): string
    {
        return Notification::class;
    }

    public function serialize(Notification $notification, array $options): array
    {
        return [
            'id' => $notification->getUuid(),
            'thumbnail' => $notification->getThumbnail(),
            'message' => $notification->getMessage(),
            'date' => DateNormalizer::normalize($notification->getCreatedAt()),
        ];
    }
}

<?php

namespace  Icap\NotificationBundle\Serializer;

use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Icap\NotificationBundle\Entity\Notification;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @DI\Service("claroline.serializer.notification")
 * @DI\Tag("claroline.serializer")
 */
class NotificationSerializer
{
    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *      "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function serialize(Notification $notification)
    {
        return [
            'id' => $notification->getId(),
            'action' => $notification->getActionKey(),
            'creation' => DateNormalizer::normalize($notification->getCreationDate()),
        ];
    }

    public function getClass()
    {
        return Notification::class;
    }
}

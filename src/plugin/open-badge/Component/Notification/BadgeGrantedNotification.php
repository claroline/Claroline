<?php

namespace Claroline\OpenBadgeBundle\Component\Notification;

use Claroline\NotificationBundle\Component\Notification\AbstractNotification;
use Claroline\OpenBadgeBundle\Event\AddBadgeEvent;
use Claroline\OpenBadgeBundle\Event\BadgeEvents;

/**
 * Notify user when they obtain a new badge.
 */
class BadgeGrantedNotification extends AbstractNotification
{
    public static function getName(): string
    {
        return 'badge.grant';
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BadgeEvents::ADD_BADGE => 'notifyGrant',
        ];
    }

    public function notifyGrant(AddBadgeEvent $event): void
    {
        $user = $event->getUser();
        $badge = $event->getBadge();

        $this->notify('Vous avez obtenu un nouveau badge', [$user]);
    }
}

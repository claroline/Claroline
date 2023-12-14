<?php

namespace Icap\NotificationBundle\Listener;

use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Icap\NotificationBundle\Manager\NotificationManager;

/**
 * @deprecated
 */
class LogListener
{
    public function __construct(
        private readonly NotificationManager $notificationManager,
        private readonly PlatformConfigurationHandler $ch
    ) {
    }

    public function onLog($event): void
    {
        if ($event instanceof NotifiableInterface && $this->ch->getParameter('is_notification_active')) {
            $workspace = $event->getWorkspace();
            if ($event->isAllowedToNotify() && (null === $workspace || $workspace->hasNotifications())) {
                $this->notificationManager->createNotificationAndNotify($event);
            }
        }
    }
}

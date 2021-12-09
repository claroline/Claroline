<?php

namespace Icap\NotificationBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Icap\NotificationBundle\Manager\NotificationManager as NotificationManager;

class LogListener
{
    private $notificationManager;
    private $ch;

    public function __construct(
        NotificationManager $notificationManager,
        PlatformConfigurationHandler $ch
    ) {
        $this->notificationManager = $notificationManager;
        $this->ch = $ch;
    }

    public function onLog(LogGenericEvent $event)
    {
        if ($event instanceof NotifiableInterface && $this->ch->getParameter('is_notification_active')) {
            $workspace = $event->getWorkspace();
            if ($event->isAllowedToNotify() && (null === $workspace || $workspace->hasNotifications())) {
                $this->notificationManager->createNotificationAndNotify($event);
            }
        }
    }
}

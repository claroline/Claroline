<?php

namespace Icap\NotificationBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Icap\NotificationBundle\Manager\NotificationManager as NotificationManager;

/**
 * @DI\Service
 */
class LogListener
{
    private $notificationManager;
    private $ch;

    /**
     * @DI\InjectParams({
     *     "notificationManager" = @DI\Inject("icap.notification.manager"),
     *     "ch"                  = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        NotificationManager $notificationManager,
        PlatformConfigurationHandler $ch
    ) {
        $this->notificationManager = $notificationManager;
        $this->ch = $ch;
    }

    /**
     * @DI\Observe("log")
     *
     * @param LogGenericEvent $event
     */
    public function onLog(LogGenericEvent $event)
    {
        if ($event instanceof NotifiableInterface && $this->ch->getParameter('is_notification_active')) {
            if ($event->isAllowedToNotify()) {
                $this->notificationManager->createNotificationAndNotify($event);
            }
        }
    }
}

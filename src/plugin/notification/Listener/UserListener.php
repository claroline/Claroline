<?php

namespace Icap\NotificationBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Icap\NotificationBundle\Manager\NotificationUserParametersManager;

/**
 * Class UserListener.
 */
class UserListener
{
    /** @var NotificationUserParametersManager */
    private $manager;

    /**
     * @param NotificationUserParametersManager $manager
     */
    public function __construct(NotificationUserParametersManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of NotificationUserParameters nodes
        $notificationUserParameterCount = $this->manager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapNotificationBundle] updated NotificationUserParameters count: $notificationUserParameterCount");
    }
}

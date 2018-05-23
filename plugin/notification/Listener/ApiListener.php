<?php

namespace Icap\NotificationBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Icap\NotificationBundle\Manager\NotificationUserParametersManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var NotificationUserParametersManager */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("icap.notification.manager.notification_user_parameters")
     * })
     *
     * @param NotificationUserParametersManager $manager
     */
    public function __construct(NotificationUserParametersManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of NotificationUserParameters nodes
        $notificationUserParameterCount = $this->manager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[IcapNotificationBundle] updated NotificationUserParameters count: $notificationUserParameterCount");
    }
}

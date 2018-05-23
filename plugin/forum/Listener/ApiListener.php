<?php

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\ForumBundle\Manager\Manager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var Manager */
    private $manager;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.forum_manager")
     * })
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
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
        // Replace user of Subject nodes
        $subjectCount = $this->manager->replaceSubjectUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineForumBundle] updated Subject count: $subjectCount");

        // Replace user of Notification nodes
        $notificationCount = $this->manager->replaceNotificationUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineForumBundle] updated Notification count: $notificationCount");

        // Replace user of Message nodes
        $messageCount = $this->manager->replaceMessageUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineForumBundle] updated Message count: $messageCount");
    }
}

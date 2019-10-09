<?php

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\ForumBundle\Manager\Manager;

/**
 * Class ApiListener.
 */
class ApiListener
{
    /** @var Manager */
    private $manager;

    /**
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Subject nodes
        $subjectCount = $this->manager->replaceSubjectUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineForumBundle] updated Subject count: $subjectCount");

        // Replace user of Message nodes
        $messageCount = $this->manager->replaceMessageUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineForumBundle] updated Message count: $messageCount");
    }
}

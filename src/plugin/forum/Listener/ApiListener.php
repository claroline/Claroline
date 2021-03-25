<?php

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\ForumBundle\Manager\ForumManager;

/**
 * Class ApiListener.
 */
class ApiListener
{
    /** @var ForumManager */
    private $manager;

    public function __construct(ForumManager $manager)
    {
        $this->manager = $manager;
    }

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

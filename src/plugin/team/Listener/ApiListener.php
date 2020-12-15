<?php

namespace Claroline\TeamBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\TeamBundle\Manager\TeamManager;

/**
 * Class ApiListener.
 */
class ApiListener
{
    /** @var TeamManager */
    private $manager;

    /**
     * @param TeamManager $manager
     */
    public function __construct(TeamManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace team manager of Team nodes
        $teamManagerCount = $this->manager->replaceManager($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineTeamBundle] updated Team count (by team manager): $teamManagerCount");

        // Replace user of Team nodes
        $teamUserCount = $this->manager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineTeamBundle] updated Team count (by user): $teamUserCount");
    }
}

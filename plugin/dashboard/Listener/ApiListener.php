<?php

namespace Claroline\DashboardBundle\Listener;

use Claroline\CoreBundle\Event\User\MergeUsersEvent;
use Claroline\DashboardBundle\Manager\DashboardManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Class ApiListener.
 *
 * @DI\Service
 */
class ApiListener
{
    /** @var DashboardManager */
    private $dashboardManager;

    /**
     * @DI\InjectParams({
     *     "dashboardManager" = @DI\Inject("claroline.manager.dashboard_manager")
     * })
     *
     * @param DashboardManager $dashboardManager
     */
    public function __construct(DashboardManager $dashboardManager)
    {
        $this->dashboardManager = $dashboardManager;
    }

    /**
     * @DI\Observe("merge_users")
     *
     * @param MergeUsersEvent $event
     */
    public function onMerge(MergeUsersEvent $event)
    {
        // Replace user of Dashboard nodes
        $dashboardCount = $this->dashboardManager->replaceUser($event->getRemoved(), $event->getKept());
        $event->addMessage("[ClarolineDashboardBundle] updated Dashboard count: $dashboardCount");
    }
}

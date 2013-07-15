<?php
namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CoreBundle\Repository\LogRepository;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.analytics_manager")
 */
class AnalyticsManager
{
    /** @var AbstractResourceRepository */
    private $resourceRepo;
    /** @var UserRepository */
    private $userRepo;
    /** @var WorkspaceRepository */
    private $workspaceRepo;
    /** @var LogRepository */
    private $logRepository;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ObjectManager $om
    )
    {
        $this->om = $om;
        $this->resourceRepo = $om->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
        $this->workspaceRepo = $om->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
        $this->logRepository = $om->getRepository('ClarolineCoreBundle:Logger\Log');
        
    }

    public function getDefaultRange()
    {
        //By default last thirty days :
        $startDate = new \DateTime('now');
        $startDate->setTime(0, 0, 0);
        $startDate->sub(new \DateInterval('P29D')); // P29D means a period of 29 days

        $endDate = new \DateTime('now');
        $endDate->setTime(23, 59, 59);

        return array($startDate->getTimestamp(), $endDate->getTimestamp());
    }

    public function getYesterdayRange()
    {
        //By default last thirty days :
        $startDate = new \DateTime('now');
        $startDate->setTime(0, 0, 0);
        $startDate->sub(new \DateInterval('P1D')); // P1D means a period of 1 days

        $endDate = new \DateTime('now');
        $endDate->setTime(23, 59, 59);
        $endDate->sub(new \DateInterval('P1D')); // P1D means a period of 1 days

        return array($startDate->getTimestamp(), $endDate->getTimestamp());
    }

    public function getDailyActionNumberForDateRange($range = null, $action = null, $unique = false)
    {
        if ($action === null) $action = 'all';
        if ($range === null) $range = $this->getDefaultRange();
        $userSearch = null;
        $workspaceIds = null;
        $actionsRestriction = null;
        $chartData = $this->logRepository->countByDayFilteredLogs(
            $action,
            $range,
            $userSearch,
            $actionsRestriction,
            $workspaceIds,
            $unique
        );
        return array(
            "chartData" => $chartData,
            "range" => $range
        );
    }

    public function getTopByCriteria ($range = null, $top_type = null, $max = 30)
    {
        if($top_type == null) {
            $top_type = 'top_users_connections';
        }
        $listData = array();

        switch ($top_type) {
            case "top_extension":
                $listData = $this->resourceRepo->findMimeTypesWithMostResources($max);
                break;
            case "top_workspaces_resources":
                $listData = $this->workspaceRepo->findWorkspacesWithMostResources($max);
                break;
            case "top_workspaces_connections":
                $listData = $this->topWSByAction($range, 'ws_tool_read', $max);
                break;
            case "top_resources_views":
                $listData = $this->topResourcesByAction($range, 'resource_read', $max);
                break;
            case "top_resources_downloads":
                $listData = $this->topResourcesByAction($range, 'resource_export', $max);
                break;
            case "top_users_connections":
                $listData = $this->topUsersByAction($range, 'user_login', $max);
                break;
            case "top_users_workspaces_enrolled":
                $listData = $this->userRepo->findUsersEnrolledInMostWorkspaces($max);
                break;
            case "top_users_workspaces_owners":
                $listData = $this->userRepo->findUsersOwnersOfMostWorkspaces($max);
                break;
            case "top_media_views":
                $listData = $this->topMediaByAction($range, 'resource_read', $max);
                break;

        }

        return $listData;
    }

    public function topWSByAction($range = null, $action=null, $max = -1)
    {
        if($range === null) $range = $this->getYesterdayRange();
        if($action === null) $action = 'ws_tool_read';
        $resultData = $this->logRepository->topWSByAction($range, $action, $max);

        return $resultData;
    }

    public function topMediaByAction($range = null, $action=null, $max = -1)
    {
        if($range === null) $range = $this->getYesterdayRange();
        if($action === null) $action = 'resource_read';
        $resultData = $this->logRepository->topMediaByAction($range, $action, $max);

        return $resultData;
    }

    public function topResourcesByAction($range = null, $action=null, $max = -1)
    {
        if($range === null) $range = $this->getYesterdayRange();
        if($action === null) $action = 'resource_read';
        $resultData = $this->logRepository->topResourcesByAction($range, $action, $max);

        return $resultData;
    }

    public function topUsersByAction($range = null, $action=null, $max = -1)
    {
        if($range === null) $range = $this->getYesterdayRange();
        if($action === null) $action = 'user_login';
        $resultData = $this->logRepository->topUsersByAction($range, $action, $max);

        return $resultData;
    }

    public function getActiveUsers()
    {
        $resultData = $this->logRepository->activeUsers();

        return $resultData;
    }
}
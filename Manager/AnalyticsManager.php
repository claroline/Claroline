<?php
namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Event\Log\LogResourceExportEvent;
use Claroline\CoreBundle\Event\Log\LogResourceReadEvent;
use Claroline\CoreBundle\Event\Log\LogUserLoginEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use Claroline\CoreBundle\Repository\Log\LogRepository;
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
     *     "objectManager" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->om            = $objectManager;
        $this->resourceRepo  = $objectManager->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $this->userRepo      = $objectManager->getRepository('ClarolineCoreBundle:User');
        $this->workspaceRepo = $objectManager->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace');
        $this->logRepository = $objectManager->getRepository('ClarolineCoreBundle:Log\Log');

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
        if ($action === null) {
            $action = '';
        }

        if ($range === null) {
            $range = $this->getDefaultRange();
        }

        $userSearch = null;
        $workspaceIds = null;
        $actionRestriction = null;
        $chartData = $this->logRepository->countByDayFilteredLogs(
            $action,
            $range,
            $userSearch,
            $actionRestriction,
            $workspaceIds,
            $unique
        );

        return $chartData;
    }

    public function getTopByCriteria ($range = null, $topType = null, $max = 30)
    {
        if ($topType == null) {
            $topType = 'top_users_connections';
        }
        $listData = array();

        switch ($topType) {
            case 'top_extension':
                $listData = $this->resourceRepo->findMimeTypesWithMostResources($max);
                break;
            case 'top_workspaces_resources':
                $listData = $this->workspaceRepo->findWorkspacesWithMostResources($max);
                break;
            case 'top_workspaces_connections':
                $listData = $this->topWSByAction($range, LogWorkspaceToolReadEvent::ACTION, $max);
                break;
            case 'top_resources_views':
                $listData = $this->topResourcesByAction($range, LogResourceReadEvent::ACTION, $max);
                break;
            case 'top_resources_downloads':
                $listData = $this->topResourcesByAction($range, LogResourceExportEvent::ACTION, $max);
                break;
            case 'top_users_connections':
                $listData = $this->topUsersByAction($range, LogUserLoginEvent::ACTION, $max);
                break;
            case 'top_users_workspaces_enrolled':
                $listData = $this->userRepo->findUsersEnrolledInMostWorkspaces($max);
                break;
            case 'top_users_workspaces_owners':
                $listData = $this->userRepo->findUsersOwnersOfMostWorkspaces($max);
                break;
            case 'top_media_views':
                $listData = $this->topMediaByAction($range, LogResourceReadEvent::ACTION, $max);
                break;
        }

        return $listData;
    }

    public function topWSByAction($range = null, $action = null, $max = -1)
    {
        if ($range === null) {
            $range = $this->getYesterdayRange();
        }

        if ($action === null) {
            $action = LogWorkspaceToolReadEvent::ACTION;
        }

        $resultData = $this->logRepository->topWSByAction($range, $action, $max);

        return $resultData;
    }

    public function topMediaByAction($range = null, $action = null, $max = -1)
    {
        if ($range === null) {
            $range = $this->getYesterdayRange();
        }

        if ($action === null) {
            $action = LogResourceReadEvent::ACTION;
        }

        $resultData = $this->logRepository->topMediaByAction($range, $action, $max);

        return $resultData;
    }

    public function topResourcesByAction($range = null, $action = null, $max = -1)
    {
        if ($range === null) {
            $range = $this->getYesterdayRange();
        }

        if ($action === null) {
            $action = LogResourceReadEvent::ACTION;
        }

        $resultData = $this->logRepository->topResourcesByAction($range, $action, $max);

        return $resultData;
    }

    public function topUsersByAction($range = null, $action = null, $max = -1)
    {
        if ($range === null) {
            $range = $this->getYesterdayRange();
        }

        if ($action === null) {
            $action = LogUserLoginEvent::ACTION;
        }

        $resultData = $this->logRepository->topUsersByAction($range, $action, $max);

        return $resultData;
    }

    /**
     * Retrieve user who connected at least one time on the application
     *
     * @return mixed
     */
    public function getActiveUsers()
    {
        $resultData = $this->logRepository->activeUsers();

        return $resultData;
    }
}

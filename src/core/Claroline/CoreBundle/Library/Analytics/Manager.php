<?php
namespace Claroline\CoreBundle\Library\Analytics;

use Claroline\CoreBundle\Repository\AbstractResourceRepository;
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CoreBundle\Repository\WorkspaceRepository;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @DI\Service("claroline.analytics.manager")
 */
class Manager
{
    private $container;
    private $resourceRepo;
    private $userRepo;
    private $workspaceRepo;

    /**
     * @DI\InjectParams({
     *     "container"      = @DI\Inject("service_container"),
     *     "resourceRepo"   = @DI\Inject("resource_repository"),
     *     "userRepo"       = @DI\Inject("user_repository"),
     *     "workspaceRepo"  = @DI\Inject("claroline.repository.workspace_repository")
     * })
     */
    public function __construct(
        $container,
        AbstractResourceRepository $resourceRepo,
        UserRepository $userRepo,
        WorkspaceRepository $workspaceRepo
    )
    {
        $this->container = $container;
        $this->resourceRepo = $resourceRepo;
        $this->userRepo = $userRepo;
        $this->workspaceRepo = $workspaceRepo;
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
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('ClarolineCoreBundle:Logger\Log');
        $chartData = $repository->countByDayFilteredLogs(
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
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('ClarolineCoreBundle:Logger\Log');
        $resultData = $repository->topWSByAction($range, $action, $max);

        return $resultData;
    }

    public function topMediaByAction($range = null, $action=null, $max = -1)
    {
        if($range === null) $range = $this->getYesterdayRange();
        if($action === null) $action = 'resource_read';
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('ClarolineCoreBundle:Logger\Log');
        $resultData = $repository->topMediaByAction($range, $action, $max);

        return $resultData;
    }

    public function topResourcesByAction($range = null, $action=null, $max = -1)
    {
        if($range === null) $range = $this->getYesterdayRange();
        if($action === null) $action = 'resource_read';
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('ClarolineCoreBundle:Logger\Log');
        $resultData = $repository->topResourcesByAction($range, $action, $max);

        return $resultData;
    }

    public function topUsersByAction($range = null, $action=null, $max = -1)
    {
        if($range === null) $range = $this->getYesterdayRange();
        if($action === null) $action = 'user_login';
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('ClarolineCoreBundle:Logger\Log');
        $resultData = $repository->topUsersByAction($range, $action, $max);

        return $resultData;
    }

    public function getActiveUsers()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('ClarolineCoreBundle:Logger\Log');
        $resultData = $repository->activeUsers();

        return $resultData;
    }
}
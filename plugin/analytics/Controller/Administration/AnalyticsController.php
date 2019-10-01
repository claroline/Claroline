<?php

namespace Claroline\AnalyticsBundle\Controller\Administration;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/tools/admin/analytics")
 * @SEC\PreAuthorize("canOpenAdminTool('dashboard')")
 */
class AnalyticsController
{
    /** @var AnalyticsManager */
    private $analyticsManager;

    /** @var User */
    private $loggedUser;

    /**
     * AnalyticsController constructor.
     *
     * @param AnalyticsManager $analyticsManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AnalyticsManager $analyticsManager
    ) {
        $this->loggedUser = $tokenStorage->getToken()->getUser();
        $this->analyticsManager = $analyticsManager;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("", name="apiv2_admin_tool_analytics_overview")
     * @Method("GET")
     */
    public function overviewAction()
    {
        $query = $this->addOrganizationFilter([]);
        $lastMonthActions = $this->analyticsManager->getDailyActions($query);
        $query['limit'] = 5;
        $mostViewedWS = $this->analyticsManager->topWorkspaceByAction($query);
        $mostViewedMedia = $this->analyticsManager->topResourcesByAction($query, true);
        $query['filters']['action'] = 'resource-export';
        $mostDownloadedResources = $this->analyticsManager->topResourcesByAction($query);
        $usersCount = $this->analyticsManager->userRolesData($this->getLoggedUserOrganizations());
        $totalUsers = array_shift($usersCount)['total'];

        return new JsonResponse([
            'activity' => $lastMonthActions,
            'top' => [
                'workspace' => $mostViewedWS,
                'media' => $mostViewedMedia,
                'download' => $mostDownloadedResources,
            ],
            'users' => $usersCount,
            'totalUsers' => $totalUsers,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Route("/audience", name="apiv2_admin_tool_analytics_audience")
     * @Method("GET")
     */
    public function audienceAction(Request $request)
    {
        $query = $this->addOrganizationFilter($request->query->all());
        $query['hiddenFilters']['action'] = 'user-login';
        $connections = $this->analyticsManager->getDailyActions($query);
        $totalConnections = array_sum(array_map(function ($item) {
            return $item['yData'];
        }, $connections));
        $activeUsersForPeriod = $this->analyticsManager->countActiveUsers($query, true);
        $activeUsers = $this->analyticsManager->countActiveUsers();
        $dates = array_column($connections, 'xData');

        return new JsonResponse([
            'activity' => [
                'daily' => $connections,
                'total' => $totalConnections,
            ],
            'users' => [
                'all' => $activeUsers,
                'period' => $activeUsersForPeriod,
            ],
            'filters' => [
                'dateLog' => $dates[0],
                'dateTo' => $dates[sizeof($connections) - 1],
                'unique' => isset($query['filters']['unique']) ?
                    filter_var($query['filters']['unique'], FILTER_VALIDATE_BOOLEAN) :
                    false,
            ],
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/resources", name="apiv2_admin_tool_analytics_resources")
     * @Method("GET")
     */
    public function resourcesAction()
    {
        $organizations = $this->getLoggedUserOrganizations();
        $wsCount = $this->analyticsManager->countNonPersonalWorkspaces($organizations);
        $resourceCount = $this->analyticsManager->getResourceTypesCount(null, $organizations);
        $otherResources = [];
        if (null === $organizations) {
            $otherResources = $this->analyticsManager->getOtherResourceTypesCount();
        }

        return new JsonResponse([
            'resources' => $resourceCount,
            'workspaces' => $wsCount,
            'other' => $otherResources,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/widgets", name="apiv2_admin_tool_analytics_widgets")
     * @Method("GET")
     */
    public function widgetsAction()
    {
        $organizations = $this->getLoggedUserOrganizations();

        return new JsonResponse($this->analyticsManager->getWidgetsData($organizations));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/top", name="apiv2_admin_tool_analytics_top_actions")
     * @Method("GET")
     */
    public function topActionsAction(Request $request)
    {
        $query = $this->addOrganizationFilter($request->query->all());

        return new JsonResponse($this->analyticsManager->getTopActions($query));
    }

    private function addOrganizationFilter($query)
    {
        $organizations = $this->getLoggedUserOrganizations();
        if (null !== $organizations) {
            $query['hiddenFilters']['organization'] = $this->loggedUser->getAdministratedOrganizations();
        }

        return $query;
    }

    private function getLoggedUserOrganizations()
    {
        if (!$this->loggedUser->hasRole('ROLE_ADMIN')) {
            return $this->loggedUser->getAdministratedOrganizations();
        }

        return null;
    }
}

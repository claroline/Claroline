<?php

namespace Claroline\AnalyticsBundle\Controller\Administration;

use Claroline\AnalyticsBundle\Manager\AnalyticsManager;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractSecurityController;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Manager\EventManager;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/tools/admin/analytics")
 */
class DashboardController extends AbstractSecurityController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AnalyticsManager */
    private $analyticsManager;

    /** @var EventManager */
    private $eventManager;

    /** @var FinderProvider */
    private $finder;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder,
        AnalyticsManager $analyticsManager,
        EventManager $eventManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
        $this->analyticsManager = $analyticsManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @Route("/activity", name="apiv2_admin_tool_analytics_activity")
     *
     * @return JsonResponse
     */
    public function activityAction(Request $request)
    {
        $this->canOpenAdminTool('dashboard');

        $query = $this->addOrganizationFilter($request->query->all());

        return new JsonResponse([
            'actions' => $this->analyticsManager->getDailyActions($query),
            'visitors' => $this->analyticsManager->getDailyActions(array_merge_recursive($query, [
                'hiddenFilters' => [
                    'action' => 'user-login',
                    'unique' => true,
                ],
            ])),
        ]);
    }

    /**
     * @Route("/actions", name="apiv2_admin_tool_analytics_actions")
     *
     * @return JsonResponse
     */
    public function actionsAction(Request $request)
    {
        $this->canOpenAdminTool('dashboard');

        $query = $this->addOrganizationFilter($request->query->all());

        return new JsonResponse([
            'types' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_ADMIN),
            'actions' => $this->analyticsManager->getDailyActions($query),
        ]);
    }

    /**
     * @Route("/time", name="apiv2_admin_tool_analytics_time")
     *
     * @return JsonResponse
     */
    public function connectionTimeAction()
    {
        $this->canOpenAdminTool('dashboard');

        return new JsonResponse([
            'total' => [],
            'average' => [],
        ]);
    }

    /**
     * @Route("/resources", name="apiv2_admin_tool_analytics_resources")
     *
     * @return JsonResponse
     */
    public function resourcesAction()
    {
        $this->canOpenAdminTool('dashboard');

        return new JsonResponse(
            $this->analyticsManager->getResourceTypesCount(null, $this->getLoggedUserOrganizations())
        );
    }

    /**
     * @Route("/resources/top", name="apiv2_admin_tool_analytics_top_resources")
     *
     * @return JsonResponse
     */
    public function topResourcesAction()
    {
        $this->canOpenAdminTool('dashboard');

        $options = [
            'page' => 0,
            'limit' => 10,
            'sortBy' => '-viewsCount',
            'hiddenFilters' => [
                'published' => true,
                'resourceTypeBlacklist' => ['directory'],
            ],
        ];

        $roles = $this->tokenStorage->getToken()->getRoleNames();

        if (!in_array('ROLE_ADMIN', $roles)) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, $options)['data']
        );
    }

    /**
     * @Route("/users", name="apiv2_admin_tool_analytics_users")
     *
     * @return JsonResponse
     */
    public function usersAction()
    {
        $this->canOpenAdminTool('dashboard');

        return new JsonResponse(
            $this->analyticsManager->userRolesData(null, $this->getLoggedUserOrganizations())
        );
    }

    /**
     * @Route("/users/top", name="apiv2_admin_tool_analytics_top_users")
     *
     * @return JsonResponse
     */
    public function topUsersAction()
    {
        $this->canOpenAdminTool('dashboard');

        $options = [
            'page' => 0,
            'limit' => 10,
            'sortBy' => '-created',
        ];

        return new JsonResponse(
            $this->finder->search(User::class, $this->addOrganizationFilter($options))['data']
        );
    }

    private function addOrganizationFilter($query)
    {
        $this->canOpenAdminTool('dashboard');

        if (!isset($query['hiddenFilters'])) {
            $query['hiddenFilters'] = [];
        }

        $organizations = $this->getLoggedUserOrganizations();
        if (null !== $organizations) {
            $query['hiddenFilters']['organization'] = $organizations;
        }

        return $query;
    }

    private function getLoggedUserOrganizations()
    {
        if (!in_array(PlatformRoles::ADMIN, $this->tokenStorage->getToken()->getRoleNames())) {
            return $this->tokenStorage->getToken()->getUser()->getAdministratedOrganizations();
        }

        return null;
    }
}

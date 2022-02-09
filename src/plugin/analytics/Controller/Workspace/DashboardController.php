<?php

namespace Claroline\AnalyticsBundle\Controller\Workspace;

use Claroline\AnalyticsBundle\Manager\AnalyticsManager;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Manager\EventManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/workspace/dashboard")
 */
class DashboardController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var FinderProvider */
    private $finder;
    /** @var AnalyticsManager */
    private $analyticsManager;
    /** @var EventManager */
    private $eventManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        FinderProvider $finder,
        AnalyticsManager $analyticsManager,
        EventManager $eventManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
        $this->analyticsManager = $analyticsManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @Route("/{workspace}/activity", name="apiv2_workspace_analytics_activity", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function activityAction(Workspace $workspace, Request $request): JsonResponse
    {
        if (!$this->checkDashboardToolAccess($workspace)) {
            throw new AccessDeniedException();
        }

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'workspace' => $workspace,
        ];

        return new JsonResponse([
            'actions' => $this->analyticsManager->getDailyActions($query),
            'visitors' => $this->analyticsManager->getDailyActions(array_merge_recursive($query, [
                'hiddenFilters' => [
                    'action' => LogWorkspaceEnterEvent::ACTION,
                    'unique' => true,
                ],
            ])),
        ]);
    }

    /**
     * @Route("/{workspace}/actions", name="apiv2_workspace_analytics_actions", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function actionsAction(Workspace $workspace, Request $request): JsonResponse
    {
        if (!$this->checkDashboardToolAccess($workspace)) {
            throw new AccessDeniedException();
        }

        $query = $request->query->all();
        $query['hiddenFilters'] = [
            'workspace' => $workspace,
        ];

        return new JsonResponse([
            'types' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_WORKSPACE),
            'actions' => $this->analyticsManager->getDailyActions($query),
        ]);
    }

    /**
     * @Route("/{workspace}/time", name="apiv2_workspace_analytics_time", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function connectionTimeAction(Workspace $workspace): JsonResponse
    {
        if (!$this->checkDashboardToolAccess($workspace)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse([
            'total' => [],
            'average' => [],
        ]);
    }

    /**
     * @Route("/{workspace}/resources", name="apiv2_workspace_analytics_resources", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function resourcesAction(Workspace $workspace): JsonResponse
    {
        if (!$this->checkDashboardToolAccess($workspace)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(
            $this->analyticsManager->getResourceTypesCount($workspace)
        );
    }

    /**
     * @Route("/{workspace}/resources/top", name="apiv2_workspace_analytics_top_resources", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function topResourcesAction(Workspace $workspace): JsonResponse
    {
        if (!$this->checkDashboardToolAccess($workspace)) {
            throw new AccessDeniedException();
        }

        $options = [
            'page' => 0,
            'limit' => 10,
            'sortBy' => '-viewsCount',
            'hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
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
     * @Route("/{workspace}/users", name="apiv2_workspace_analytics_users", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function usersAction(Workspace $workspace): JsonResponse
    {
        if (!$this->checkDashboardToolAccess($workspace)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(
            $this->analyticsManager->userRolesData($workspace)
        );
    }

    /**
     * @Route("/{workspace}/users/top", name="apiv2_workspace_analytics_top_users", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     */
    public function topUsersAction(Workspace $workspace): JsonResponse
    {
        if (!$this->checkDashboardToolAccess($workspace)) {
            throw new AccessDeniedException();
        }

        $options = [
            'page' => 0,
            'limit' => 10,
            'sortBy' => '-created',
            'hiddenFilters' => [
                'workspace' => $workspace->getUuid(),
            ],
        ];

        return new JsonResponse(
            $this->finder->search(User::class, $options)['data']
        );
    }

    /**
     * Checks user rights to access logs tool.
     */
    private function checkDashboardToolAccess(Workspace $workspace): bool
    {
        if ($this->authorization->isGranted(['dashboard', 'OPEN'], $workspace)) {
            return true;
        }

        return false;
    }
}

<?php

namespace Claroline\AnalyticsBundle\Controller\Workspace;

use Claroline\AnalyticsBundle\Manager\AnalyticsManager;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Manager\EventManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\Role;

/**
 * @EXT\Route("/dashboard")
 */
class DashboardController
{
    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ObjectManager */
    private $om;

    /** @var FinderProvider */
    private $finder;

    /** @var AnalyticsManager */
    private $analyticsManager;

    /** @var EventManager */
    private $eventManager;

    /**
     * DashboardController constructor.
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param TokenStorageInterface         $tokenStorage
     * @param ObjectManager                 $om
     * @param SerializerProvider            $serializer
     * @param FinderProvider                $finder
     * @param AnalyticsManager              $analyticsManager
     * @param EventManager                  $eventManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        SerializerProvider $serializer,
        FinderProvider $finder,
        AnalyticsManager $analyticsManager,
        EventManager $eventManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->finder = $finder;
        $this->analyticsManager = $analyticsManager;
        $this->eventManager = $eventManager;
    }

    /**
     * @EXT\Route("/{workspace}/activity", name="apiv2_workspace_analytics_activity")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function activityAction(Workspace $workspace, Request $request)
    {
        if (!$this->checkDashboardToolAccess('OPEN', $workspace)) {
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
     * @EXT\Route("/{workspace}/actions", name="apiv2_workspace_analytics_actions")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function actionsAction(Workspace $workspace, Request $request)
    {
        if (!$this->checkDashboardToolAccess('OPEN', $workspace)) {
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
     * @EXT\Route("/{workspace}/time", name="apiv2_workspace_analytics_time")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function connectionTimeAction(Workspace $workspace)
    {
        if (!$this->checkDashboardToolAccess('OPEN', $workspace)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse([
            'total' => [],
            'average' => [],
        ]);
    }

    /**
     * @EXT\Route("/{workspace}/resources", name="apiv2_workspace_analytics_resources")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function resourcesAction(Workspace $workspace)
    {
        if (!$this->checkDashboardToolAccess('OPEN', $workspace)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(
            $this->analyticsManager->getResourceTypesCount($workspace)
        );
    }

    /**
     * @EXT\Route("/{workspace}/resources/top", name="apiv2_workspace_analytics_top_resources")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function topResourcesAction(Workspace $workspace)
    {
        if (!$this->checkDashboardToolAccess('OPEN', $workspace)) {
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

        $roles = array_map(function (Role $role) {
            return $role->getRole();
        }, $this->tokenStorage->getToken()->getRoles());

        if (!in_array('ROLE_ADMIN', $roles)) {
            $options['hiddenFilters']['roles'] = $roles;
        }

        return new JsonResponse(
            $this->finder->search(ResourceNode::class, $options)['data']
        );
    }

    /**
     * @EXT\Route("/{workspace}/users", name="apiv2_workspace_analytics_users")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function usersAction(Workspace $workspace)
    {
        if (!$this->checkDashboardToolAccess('OPEN', $workspace)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(
            $this->analyticsManager->userRolesData($workspace)
        );
    }

    /**
     * @EXT\Route("/{workspace}/users/top", name="apiv2_workspace_analytics_top_users")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     *
     * @return JsonResponse
     */
    public function topUsersAction(Workspace $workspace)
    {
        if (!$this->checkDashboardToolAccess('OPEN', $workspace)) {
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
     * @EXT\Route("/{workspace}/progression/{user}", name="apiv2_workspace_get_user_progression")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", class="Claroline\CoreBundle\Entity\User", options={"mapping": {"user": "uuid"}})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"workspace": "uuid"}})
     *
     * @param Workspace $workspace
     * @param User      $user
     *
     * @return JsonResponse
     */
    public function getUserProgressionAction(Workspace $workspace, User $user)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        if (!$this->checkDashboardToolAccess('EDIT', $workspace) && (!$currentUser instanceof User || $currentUser->getId() !== $user->getId())) {
            throw new AccessDeniedException();
        }

        return new JsonResponse([
            'workspaceEvaluation' => $this->serializer->serialize($this->om->getRepository(Evaluation::class)->findOneBy(['workspace' => $workspace, 'user' => $user])),
            'resourceEvaluations' => $this->finder->search(ResourceUserEvaluation::class, [
                'filters' => ['workspace' => $workspace->getUuid(), 'user' => $user->getUuid()],
            ])['data'],
        ]);
    }

    /**
     * @EXT\Route("/evaluations/{userEvaluationId}", name="apiv2_workspace_list_resource_evaluations")
     * @EXT\ParamConverter("userEvaluation", class="Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation", options={"mapping": {"userEvaluationId": "id"}})
     * @EXT\Method("GET")
     *
     * @param ResourceUserEvaluation $userEvaluation
     * @param Request                $request
     *
     * @return JsonResponse
     */
    public function listResourceEvaluationsAction(ResourceUserEvaluation $userEvaluation, Request $request)
    {
        $currentUser = $this->tokenStorage->getToken()->getUser();
        if (!$this->checkDashboardToolAccess('EDIT', $userEvaluation->getResourceNode()->getWorkspace()) && (!$currentUser instanceof User || $currentUser->getId() !== $userEvaluation->getUser()->getId())) {
            throw new AccessDeniedException();
        }

        $query = $request->query->all();
        $query['hiddenFilters'] = ['resourceUserEvaluation' => $userEvaluation];

        return new JsonResponse(
            $this->finder->search(ResourceEvaluation::class, $query)
        );
    }

    /**
     * Checks user rights to access logs tool.
     *
     * @param string    $permission
     * @param Workspace $workspace
     *
     * @return bool
     */
    private function checkDashboardToolAccess(string $permission, Workspace $workspace)
    {
        if ($this->authorization->isGranted(['dashboard', $permission], $workspace)) {
            return true;
        }

        return false;
    }
}

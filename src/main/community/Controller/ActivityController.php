<?php

namespace Claroline\CommunityBundle\Controller;

use Claroline\AnalyticsBundle\Manager\AnalyticsManager;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Repository\GroupRepository;
use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectPlatform;
use Claroline\CoreBundle\Entity\Log\Connection\LogConnectWorkspace;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogWorkspaceEnterEvent;
use Claroline\CoreBundle\Manager\EventManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Repository\Log\Connection\LogConnectPlatformRepository;
use Claroline\CoreBundle\Repository\Log\Connection\LogConnectWorkspaceRepository;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\PlatformRoles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/community/activity")
 */
class ActivityController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var ObjectManager */
    private $om;
    /** @var FinderProvider */
    private $finder;
    /** @var ToolManager */
    private $toolManager;
    /** @var EventManager */
    private $eventManager;
    /** @var AnalyticsManager */
    private $analyticsManager;

    /** @var UserRepository */
    private $userRepo;
    /** @var GroupRepository */
    private $groupRepo;
    /** @var LogConnectPlatformRepository */
    private $logConnectPlatformRepo;
    /** @var LogConnectWorkspaceRepository */
    private $logConnectWorkspaceRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om,
        FinderProvider $finder,
        ToolManager $toolManager,
        EventManager $eventManager,
        AnalyticsManager $analyticsManager
    ) {
        $this->authorization = $authorization;
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
        $this->finder = $finder;
        $this->toolManager = $toolManager;
        $this->eventManager = $eventManager;
        $this->analyticsManager = $analyticsManager;

        $this->userRepo = $om->getRepository(User::class);
        $this->groupRepo = $om->getRepository(Group::class);
        $this->logConnectPlatformRepo = $om->getRepository(LogConnectPlatform::class);
        $this->logConnectWorkspaceRepo = $om->getRepository(LogConnectWorkspace::class);
    }

    /**
     * @Route("/count/{contextId}", name="apiv2_community_activity")
     */
    public function openAction(?string $contextId = null): JsonResponse
    {
        if (!$this->checkToolAccess('SHOW_ACTIVITY', $contextId)) {
            throw new AccessDeniedException();
        }

        if ($contextId) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $contextId]);

            return new JsonResponse([
                'actionTypes' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_WORKSPACE),
                'count' => [
                    'connections' => [
                        'count' => $this->logConnectWorkspaceRepo->countConnections($workspace),
                        'avgTime' => $this->logConnectWorkspaceRepo->findAvgTime($workspace), // in seconds
                    ],
                    'users' => count($this->userRepo->findByWorkspaces([$workspace])),
                    'groups' => count($this->groupRepo->findByWorkspace($workspace)),
                ],
            ]);
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $organizations = [];
        if (!$user->hasRole(PlatformRoles::ADMIN)) {
            $organizations = $user->getOrganizations()->toArray();
        }

        return new JsonResponse([
            'actionTypes' => $this->eventManager->getEventsForApiFilter(LogGenericEvent::DISPLAYED_ADMIN),
            'count' => [
                'connections' => [
                    'count' => $this->logConnectPlatformRepo->countConnections($organizations),
                    'avgTime' => $this->logConnectPlatformRepo->findAvgTime($organizations), // in seconds
                ],
                'users' => $this->userRepo->countUsers($organizations),
                'groups' => count($this->groupRepo->findByOrganizations($organizations)),
            ],
        ]);
    }

    /**
     * @Route("/global/{contextId}", name="apiv2_community_activity_global")
     */
    public function globalAction(Request $request, ?string $contextId = null): JsonResponse
    {
        if (!$this->checkToolAccess('SHOW_ACTIVITY', $contextId)) {
            throw new AccessDeniedException();
        }

        $query = $this->filterQuery($request->query->all(), $contextId);

        return new JsonResponse([
            'actions' => $this->analyticsManager->getDailyActions($query),
            'visitors' => $this->analyticsManager->getDailyActions(array_merge_recursive($query, [
                'hiddenFilters' => [
                    'action' => $contextId ? LogWorkspaceEnterEvent::ACTION : 'user-login',
                    'unique' => true,
                ],
            ])),
        ]);
    }

    /**
     * @Route("/logs/{contextId}", name="apiv2_community_activity_logs", methods={"GET"})
     */
    public function listLogsAction(Request $request, ?string $contextId = null): JsonResponse
    {
        if (!$this->checkToolAccess('SHOW_ACTIVITY', $contextId)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(
            $this->finder->search(Log::class, $this->filterQuery($request->query->all(), $contextId))
        );
    }

    private function checkToolAccess(string $rights = 'OPEN', ?string $contextId = null): bool
    {
        if ($contextId) {
            $communityTool = $this->toolManager->getOrderedTool('community', Tool::WORKSPACE, $contextId);
        } else {
            $communityTool = $this->toolManager->getOrderedTool('community', Tool::DESKTOP);
        }

        if (is_null($communityTool) || !$this->authorization->isGranted($rights, $communityTool)) {
            return false;
        }

        return true;
    }

    private function filterQuery(array $query, ?string $contextId = null): array
    {
        if ($contextId) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $contextId]);
            $query['hiddenFilters'] = ['workspace' => $workspace];
        } else {
            $user = $this->tokenStorage->getToken()->getUser();
            if (!$user->hasRole(PlatformRoles::ADMIN)) {
                $organizations = array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->geOrganizations()->toArray());
                $query['hiddenFilters'] = ['organizations' => $organizations];
            }
        }

        return $query;
    }
}

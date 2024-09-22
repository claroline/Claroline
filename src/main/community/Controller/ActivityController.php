<?php

namespace Claroline\CommunityBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Repository\GroupRepository;
use Claroline\CommunityBundle\Repository\UserRepository;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\PlatformRoles;
use Claroline\LogBundle\Entity\FunctionalLog;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/community/activity')]
class ActivityController
{
    use PermissionCheckerTrait;

    private UserRepository $userRepo;
    private GroupRepository $groupRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om,
        private readonly FinderProvider $finder,
        private readonly ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
        $this->userRepo = $om->getRepository(User::class);
        $this->groupRepo = $om->getRepository(Group::class);
    }

    #[Route(path: '/count/{contextId}', name: 'apiv2_community_activity')]
    public function openAction(string $contextId = null): JsonResponse
    {
        if (!$this->checkToolAccess('SHOW_ACTIVITY', $contextId)) {
            throw new AccessDeniedException();
        }

        if ($contextId) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $contextId]);

            return new JsonResponse([
                'count' => [
                    'users' => count($this->userRepo->findByWorkspaces([$workspace])),
                    'groups' => count($this->groupRepo->findByWorkspace($workspace)),
                ],
            ]);
        }

        $organizations = [];
        if (!$this->authorization->isGranted(PlatformRoles::ADMIN)) {
            $user = $this->tokenStorage->getToken()->getUser();

            $organizations = $user->getOrganizations()->toArray();
        }

        return new JsonResponse([
            'count' => [
                'users' => $this->userRepo->countUsers($organizations),
                'groups' => count($this->groupRepo->findByOrganizations($organizations)),
            ],
        ]);
    }

    #[Route(path: '/logs/{contextId}', name: 'apiv2_community_functional_logs', methods: ['GET'])]
    public function functionalLogsAction(Request $request, string $contextId = null): JsonResponse
    {
        if (!$this->checkToolAccess('SHOW_ACTIVITY', $contextId)) {
            throw new AccessDeniedException();
        }

        return new JsonResponse(
            $this->finder->search(FunctionalLog::class, $this->filterQuery($request->query->all(), $contextId))
        );
    }

    private function checkToolAccess(string $rights = 'OPEN', string $contextId = null): bool
    {
        if ($contextId) {
            $communityTool = $this->toolManager->getOrderedTool('community', WorkspaceContext::getName(), $contextId);
        } else {
            $communityTool = $this->toolManager->getOrderedTool('community', DesktopContext::getName());
        }

        if (is_null($communityTool) || !$this->authorization->isGranted($rights, $communityTool)) {
            return false;
        }

        return true;
    }

    private function filterQuery(array $query, string $contextId = null): array
    {
        if (empty($query['hiddenFilters'])) {
            $query['hiddenFilters'] = [];
        }

        if ($contextId) {
            $query['hiddenFilters']['workspace'] = $contextId;
        }

        if (!$this->authorization->isGranted(PlatformRoles::ADMIN)) {
            $user = $this->tokenStorage->getToken()->getUser();

            $organizations = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getOrganizations()->toArray());
            $query['hiddenFilters']['organizations'] = $organizations;
        }

        return $query;
    }
}

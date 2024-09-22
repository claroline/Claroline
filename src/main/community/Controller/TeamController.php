<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Controller;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CommunityBundle\Manager\TeamManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\CoreBundle\Security\ToolPermissions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/team', name: 'apiv2_team_')]
class TeamController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly TeamManager $teamManager
    ) {
        $this->authorization = $authorization;
    }

    public static function getClass(): string
    {
        return Team::class;
    }

    public static function getName(): string
    {
        return 'team';
    }

    #[Route(path: '/workspace/{id}/teams', name: 'workspace_list', methods: ['GET'])]
    public function listByWorkspaceAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Workspace $workspace,
        Request $request
    ): JsonResponse {
        $this->checkToolAccess($workspace, 'open');
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['workspace'] = $workspace->getUuid();

        return new JsonResponse(
            $this->crud->list(Team::class, $params, [Options::SERIALIZE_LIST])
        );
    }

    
    #[Route(path: '/{id}/users/{role}', name: 'list_users', methods: ['GET'])]
    public function listUsersAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Team $team, string $role, #[CurrentUser] ?User $user, Request $request): JsonResponse
    {
        $this->checkPermission('OPEN', $team, [], true);

        $hiddenFilters = [
            'role' => ['manager' === $role ? $team->getManagerRole()->getUuid() : $team->getRole()->getUuid()],
        ];

        if (!$this->checkPermission('ROLE_ADMIN')) {
            // only list users for the current user organizations
            $hiddenFilters['organizations'] = array_map(function (Organization $organization) {
                return $organization->getUuid();
            }, $user->getOrganizations());
        }

        return new JsonResponse(
            $this->crud->list(User::class, array_merge($request->query->all(), [
                'hiddenFilters' => $hiddenFilters,
            ]))
        );
    }

    #[Route(path: '/{id}/users/{role}', name: 'register', methods: ['PATCH'])]
    public function registerAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Team $team, string $role, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $team, [], true);

        $users = parent::decodeIdsString($request, User::class);
        $workspace = $team->getWorkspace();

        switch ($role) {
            case 'user':
                $maxUsers = $team->getMaxUsers();

                if ($maxUsers && $maxUsers < $this->om->getRepository(Team::class)->countUsers($team) + count($users)) {
                    throw new AccessDeniedException();
                }

                $allowedTeams = $workspace->getMaxTeams();
                if ($allowedTeams) {
                    foreach ($users as $user) {
                        if ($allowedTeams <= count($this->teamManager->getTeamsByUserAndWorkspace($user, $workspace))) {
                            throw new AccessDeniedException();
                        }
                    }
                }
                $this->teamManager->registerUsersToTeam($team, $users);
                break;
            case 'manager':
                $this->teamManager->registerManagersToTeam($team, $users);
                break;
        }

        return new JsonResponse(null, 200);
    }

    #[Route(path: '/{id}/users/{role}', name: 'unregister', methods: ['DELETE'])]
    public function unregisterAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Team $team, string $role, Request $request): JsonResponse
    {
        $this->checkPermission('EDIT', $team, [], true);

        $users = parent::decodeIdsString($request, User::class);

        if ('manager' === $role) {
            $this->teamManager->unregisterManagersFromTeam($team, $users);
        } else {
            $this->teamManager->unregisterUsersFromTeam($team, $users);
        }

        return new JsonResponse(null, 200);
    }

    
    #[Route(path: '/{id}/register', name: 'self_register', methods: ['PUT'])]
    public function selfRegisterAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Team $team, #[CurrentUser] ?User $user): JsonResponse
    {
        $this->checkPermission('OPEN', $team, [], true);

        if (!$team->isSelfRegistration()) {
            throw new AccessDeniedException();
        }

        $maxUsers = $team->getMaxUsers();
        if ($maxUsers && $maxUsers < $this->om->getRepository(Team::class)->countUsers($team)) {
            throw new AccessDeniedException();
        }

        $workspace = $team->getWorkspace();
        $allowedTeams = $workspace->getMaxTeams();
        if ($allowedTeams && $allowedTeams <= count($this->teamManager->getTeamsByUserAndWorkspace($user, $workspace))) {
            throw new AccessDeniedException();
        }

        $this->teamManager->registerUsersToTeam($team, [$user]);

        return new JsonResponse(null, 200);
    }

    
    #[Route(path: '/{id}/unregister', name: 'self_unregister', methods: ['DELETE'])]
    public function selfUnregisterAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Team $team, #[CurrentUser] ?User $user): JsonResponse
    {
        $this->checkPermission('OPEN', $team, [], true);

        if (!$team->isSelfUnregistration()) {
            throw new AccessDeniedException();
        }

        $this->teamManager->unregisterUsersFromTeam($team, [$user]);

        return new JsonResponse(null, 200);
    }

    #[Route(path: '/teams/fill', name: 'fill', methods: ['PUT'])]
    public function fillAction(Request $request): JsonResponse
    {
        $teams = parent::decodeIdsString($request, Team::class);

        $this->om->startFlushSuite();
        foreach ($teams as $team) {
            if ($this->checkPermission('EDIT', $team)) {
                $this->teamManager->fillTeam($team);
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    #[Route(path: '/teams/empty', name: 'empty', methods: ['DELETE'])]
    public function emptyAction(Request $request): JsonResponse
    {
        $teams = parent::decodeIdsString($request, Team::class);

        $this->om->startFlushSuite();
        foreach ($teams as $team) {
            if ($this->checkPermission('EDIT', $team)) {
                $this->teamManager->emptyTeam($team);
            }
        }
        $this->om->endFlushSuite();

        return new JsonResponse(null, 204);
    }

    protected function getDefaultHiddenFilters(): array
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return [];
    }

    private function checkToolAccess(Workspace $workspace, string $permission): void
    {
        if (!$this->authorization->isGranted(ToolPermissions::getPermission('community', $permission), $workspace)) {
            throw new AccessDeniedException();
        }
    }
}

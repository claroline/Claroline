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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CommunityBundle\Manager\TeamManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/team")
 */
class TeamController extends AbstractCrudController
{
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var TeamManager */
    private $teamManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        TeamManager $teamManager
    ) {
        $this->authorization = $authorization;
        $this->teamManager = $teamManager;
    }

    public function getClass(): string
    {
        return Team::class;
    }

    public function getName(): string
    {
        return 'team';
    }

    public function getIgnore(): array
    {
        return ['exist', 'copyBulk', 'schema', 'find', 'list'];
    }

    /**
     * @Route("/workspace/{id}/teams", name="apiv2_workspace_team_list", methods={"GET"})
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"id": "uuid"}})
     */
    public function listByWorkspaceAction(Workspace $workspace, Request $request): JsonResponse
    {
        $this->checkToolAccess($workspace, 'open');
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['workspace'] = $workspace->getUuid();

        return new JsonResponse(
            $this->finder->search(Team::class, $params, [Options::SERIALIZE_LIST])
        );
    }

    /**
     * @Route("/{id}/users/{role}", name="apiv2_team_list_users", methods={"GET"})
     * @EXT\ParamConverter("team", class="Claroline\CommunityBundle\Entity\Team", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function listUsersAction(Team $team, string $role, User $user, Request $request): JsonResponse
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

    /**
     * @Route("/{id}/users/{role}", name="apiv2_team_register", methods={"PATCH"})
     * @EXT\ParamConverter("team", class="Claroline\CommunityBundle\Entity\Team", options={"mapping": {"id": "uuid"}})
     */
    public function registerAction(Team $team, string $role, Request $request): JsonResponse
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

    /**
     * @Route("/{id}/users/{role}", name="apiv2_team_unregister", methods={"DELETE"})
     * @EXT\ParamConverter("team", class="Claroline\CommunityBundle\Entity\Team", options={"mapping": {"id": "uuid"}})
     */
    public function unregisterAction(Team $team, string $role, Request $request): JsonResponse
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

    /**
     * @Route("/{id}/register", name="apiv2_team_self_register", methods={"PUT"})
     * @EXT\ParamConverter("team", class="Claroline\CommunityBundle\Entity\Team", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function selfRegisterAction(Team $team, User $user): JsonResponse
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

    /**
     * @Route("/{id}/unregister", name="apiv2_team_self_unregister", methods={"DELETE"})
     * @EXT\ParamConverter("team", class="Claroline\CommunityBundle\Entity\Team", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function selfUnregisterAction(Team $team, User $user): JsonResponse
    {
        $this->checkPermission('OPEN', $team, [], true);

        if (!$team->isSelfUnregistration()) {
            throw new AccessDeniedException();
        }

        $this->teamManager->unregisterUsersFromTeam($team, [$user]);

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/teams/fill", name="apiv2_team_fill", methods={"PUT"})
     */
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

    /**
     * @Route("/teams/empty", name="apiv2_team_empty", methods={"DELETE"})
     */
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

    private function checkToolAccess(Workspace $workspace, $rights): void
    {
        if (!$this->authorization->isGranted(['community', $rights], $workspace)) {
            throw new AccessDeniedException();
        }
    }
}

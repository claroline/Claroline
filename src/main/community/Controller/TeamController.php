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
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
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
     * @Route("/workspace/{id}/teams/list", name="apiv2_workspace_team_list")
     * @EXT\ParamConverter("workspace", class="Claroline\CoreBundle\Entity\Workspace\Workspace", options={"mapping": {"id": "uuid"}})
     */
    public function teamsListAction(Workspace $workspace, Request $request): JsonResponse
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
     * @Route("/team/{team}/{role}/register", name="apiv2_team_register")
     * @EXT\ParamConverter("team", class="Claroline\CommunityBundle\Entity\Team", options={"mapping": {"team": "uuid"}})
     */
    public function teamRegisterAction(Team $team, string $role, Request $request): JsonResponse
    {
        $this->checkToolAccess($team->getWorkspace(), 'edit');
        $users = parent::decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $workspace = $team->getWorkspace();

        switch ($role) {
            case 'user':
                $maxUsers = $team->getMaxUsers();

                if ($maxUsers && $maxUsers < count($team->getRole()->getUsers()->toArray()) + count($users)) {
                    throw new AccessDeniedException();
                }
               /* $params = $this->teamManager->getWorkspaceTeamParameters($workspace);
                $allowedTeams = $params->getMaxTeams();

                if ($allowedTeams) {
                    foreach ($users as $user) {
                        if ($allowedTeams <= count($this->teamManager->getTeamsByUserAndWorkspace($user, $workspace))) {
                            throw new AccessDeniedException();
                        }
                    }
                }*/
                $this->teamManager->registerUsersToTeam($team, $users);
                break;
            case 'manager':
                $this->teamManager->registerManagersToTeam($team, $users);
                break;
        }

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/team/{team}/{role}/unregister", name="apiv2_team_unregister")
     * @EXT\ParamConverter("team", class="Claroline\CommunityBundle\Entity\Team", options={"mapping": {"team": "uuid"}})
     */
    public function teamUnregisterAction(Team $team, string $role, Request $request): JsonResponse
    {
        $this->checkToolAccess($team->getWorkspace(), 'edit');
        $users = parent::decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');

        switch ($role) {
            case 'user':
                $this->teamManager->unregisterUsersFromTeam($team, $users);
                break;
            case 'manager':
                $this->teamManager->unregisterManagersFromTeam($team, $users);
                break;
        }

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/team/{team}/register", name="apiv2_team_self_register")
     * @EXT\ParamConverter("team", class="Claroline\CommunityBundle\Entity\Team", options={"mapping": {"team": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function teamSelfRegisterAction(Team $team, User $user): JsonResponse
    {
        $workspace = $team->getWorkspace();
        $this->checkToolAccess($workspace, 'open');

        if (!$team->isSelfRegistration()) {
            throw new AccessDeniedException();
        }
        $maxUsers = $team->getMaxUsers();

        if ($maxUsers && $maxUsers <= count($team->getRole()->getUsers()->toArray())) {
            throw new AccessDeniedException();
        }
        $params = $this->teamManager->getWorkspaceTeamParameters($workspace);
        $allowedTeams = $params->getMaxTeams();

        if ($allowedTeams && $allowedTeams <= count($this->teamManager->getTeamsByUserAndWorkspace($user, $workspace))) {
            throw new AccessDeniedException();
        }

        $this->teamManager->registerUsersToTeam($team, [$user]);

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/team/{team}/unregister", name="apiv2_team_self_unregister")
     * @EXT\ParamConverter("team", class="Claroline\CommunityBundle\Entity\Team", options={"mapping": {"team": "uuid"}})
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     */
    public function teamSelfUnregisterAction(Team $team, User $user): JsonResponse
    {
        $this->checkToolAccess($team->getWorkspace(), 'open');

        if (!$team->isSelfUnregistration()) {
            throw new AccessDeniedException();
        }
        $this->teamManager->unregisterUsersFromTeam($team, [$user]);

        return new JsonResponse(null, 200);
    }

    /**
     * @Route("/teams/fill", name="apiv2_team_fill")
     */
    public function teamsFillAction(Request $request): JsonResponse
    {
        $teams = parent::decodeIdsString($request, Team::class);
        $workspace = 0 < count($teams) ? $teams[0]->getWorkspace() : null;

        if ($workspace) {
            foreach ($teams as $team) {
                if ($workspace->getId() !== $team->getWorkspace()->getId()) {
                    throw new AccessDeniedException();
                }
            }
        } else {
            throw new AccessDeniedException();
        }
        $this->checkToolAccess($workspace, 'edit');
        $this->teamManager->fillTeams($workspace, $teams);

        return new JsonResponse(null, 204);
    }

    /**
     * @Route("/teams/empty", name="apiv2_team_empty")
     */
    public function teamsEmptyAction(Request $request): JsonResponse
    {
        $teams = parent::decodeIdsString($request, Team::class);
        $workspace = 0 < count($teams) ? $teams[0]->getWorkspace() : null;

        if ($workspace) {
            foreach ($teams as $team) {
                if ($workspace->getId() !== $team->getWorkspace()->getId()) {
                    throw new AccessDeniedException();
                }
            }
        } else {
            throw new AccessDeniedException();
        }
        $this->checkToolAccess($workspace, 'edit');
        $this->teamManager->emptyTeams($teams);

        return new JsonResponse(null, 204);
    }

    private function checkToolAccess(Workspace $workspace, $rights): void
    {
        if (!$this->authorization->isGranted(['community', $rights], $workspace)) {
            throw new AccessDeniedException();
        }
    }
}
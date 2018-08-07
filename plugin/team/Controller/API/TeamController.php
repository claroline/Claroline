<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Controller\API;

use Claroline\AppBundle\Annotations\ApiMeta;
use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TeamBundle\Entity\Team;
use Claroline\TeamBundle\Manager\TeamManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @ApiMeta(
 *     class="Claroline\TeamBundle\Entity\Team",
 *     ignore={"exist", "copyBulk", "schema", "find", "list"}
 * )
 * @EXT\Route("/team")
 */
class TeamController extends AbstractCrudController
{
    /** @var AuthorizationCheckerInterface */
    protected $authorization;

    /** @var FinderProvider */
    protected $finder;

    /** @var TeamManager */
    protected $teamManager;

    /**
     * TeamController constructor.
     *
     * @DI\InjectParams({
     *     "authorization" = @DI\Inject("security.authorization_checker"),
     *     "finder"        = @DI\Inject("claroline.api.finder"),
     *     "teamManager"   = @DI\Inject("claroline.manager.team_manager")
     * })
     *
     * @param AuthorizationCheckerInterface $authorization
     * @param FinderProvider                $finder
     * @param TeamManager                   $teamManager
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FinderProvider $finder,
        TeamManager $teamManager
    ) {
        $this->authorization = $authorization;
        $this->finder = $finder;
        $this->teamManager = $teamManager;
    }

    public function getName()
    {
        return 'team';
    }

    /**
     * @param Request $request
     * @param string  $class
     *
     * @return JsonResponse
     */
    public function deleteBulkAction(Request $request, $class)
    {
        $teams = parent::decodeIdsString($request, 'Claroline\TeamBundle\Entity\Team');
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
        $this->teamManager->deleteTeams($teams);

        return new JsonResponse(null, 204);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/teams/list",
     *     name="apiv2_team_list"
     * )
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function teamsListAction(Workspace $workspace, Request $request)
    {
        $this->checkToolAccess($workspace, 'open');
        $params = $request->query->all();

        if (!isset($params['hiddenFilters'])) {
            $params['hiddenFilters'] = [];
        }
        $params['hiddenFilters']['workspace'] = $workspace->getId();
        $data = $this->finder->search('Claroline\TeamBundle\Entity\Team', $params);

        return new JsonResponse($data, 200);
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/{role}/register",
     *     name="apiv2_team_register"
     * )
     * @EXT\ParamConverter(
     *     "team",
     *     class="ClarolineTeamBundle:Team",
     *     options={"mapping": {"team": "uuid"}}
     * )
     *
     * @param Team    $team
     * @param string  $role    (user|manager)
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function teamRegisterAction(Team $team, $role, Request $request)
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
                $params = $this->teamManager->getWorkspaceTeamParameters($workspace);
                $allowedTeams = $params->getMaxTeams();

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
     * @EXT\Route(
     *     "/team/{team}/{role}/unregister",
     *     name="apiv2_team_unregister"
     * )
     * @EXT\ParamConverter(
     *     "team",
     *     class="ClarolineTeamBundle:Team",
     *     options={"mapping": {"team": "uuid"}}
     * )
     *
     * @param Team    $team
     * @param string  $role    (user|manager)
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function teamUnregisterAction(Team $team, $role, Request $request)
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
     * @EXT\Route(
     *     "/team/{team}/register",
     *     name="apiv2_team_self_register"
     * )
     * @EXT\ParamConverter(
     *     "team",
     *     class="ClarolineTeamBundle:Team",
     *     options={"mapping": {"team": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Team $team
     * @param User $user
     *
     * @return JsonResponse
     */
    public function teamSelfRegisterAction(Team $team, User $user)
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
     * @EXT\Route(
     *     "/team/{team}/unregister",
     *     name="apiv2_team_self_unregister"
     * )
     * @EXT\ParamConverter(
     *     "team",
     *     class="ClarolineTeamBundle:Team",
     *     options={"mapping": {"team": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Team $team
     * @param User $user
     *
     * @return JsonResponse
     */
    public function teamSelfUnregisterAction(Team $team, User $user)
    {
        $this->checkToolAccess($team->getWorkspace(), 'open');

        if (!$team->isSelfUnregistration()) {
            throw new AccessDeniedException();
        }
        $this->teamManager->unregisterUsersFromTeam($team, [$user]);

        return new JsonResponse(null, 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/teams/create",
     *     name="apiv2_team_multiple_create"
     * )
     * @EXT\ParamConverter(
     *     "workspace",
     *     class="ClarolineCoreBundle:Workspace\Workspace",
     *     options={"mapping": {"workspace": "uuid"}}
     * )
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=false})
     *
     * @param Workspace $workspace
     * @param User      $user
     * @param Request   $request
     *
     * @return JsonResponse
     */
    public function multipleTeamsCreateAction(Workspace $workspace, User $user, Request $request)
    {
        $this->checkToolAccess($workspace, 'edit');
        $data = $request->request->all();
        $this->teamManager->createMultipleTeams($workspace, $user, $data);

        return new JsonResponse(null, 200);
    }

    /**
     * @EXT\Route(
     *     "/teams/fill",
     *     name="apiv2_team_fill"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function teamsFillAction(Request $request)
    {
        $teams = parent::decodeIdsString($request, 'Claroline\TeamBundle\Entity\Team');
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
     * @EXT\Route(
     *     "/teams/empty",
     *     name="apiv2_team_empty"
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function teamsEmptyAction(Request $request)
    {
        $teams = parent::decodeIdsString($request, 'Claroline\TeamBundle\Entity\Team');
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

    private function checkToolAccess(Workspace $workspace, $rights)
    {
        if (!$this->authorization->isGranted(['claroline_team_tool', $rights], $workspace)) {
            throw new AccessDeniedException();
        }
    }
}

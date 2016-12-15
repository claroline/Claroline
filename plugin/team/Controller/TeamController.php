<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TeamBundle\Entity\Team;
use Claroline\TeamBundle\Entity\WorkspaceTeamParameters;
use Claroline\TeamBundle\Form\MultipleTeamsType;
use Claroline\TeamBundle\Form\TeamEditType;
use Claroline\TeamBundle\Form\TeamParamsType;
use Claroline\TeamBundle\Form\TeamType;
use Claroline\TeamBundle\Manager\TeamManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeamController extends Controller
{
    private $formFactory;
    private $httpKernel;
    private $request;
    private $router;
    private $authorization;
    private $teamManager;
    private $tokenStorage;

    /**
     * @DI\InjectParams({
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "httpKernel"      = @DI\Inject("http_kernel"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "router"          = @DI\Inject("router"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "teamManager"     = @DI\Inject("claroline.manager.team_manager"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        AuthorizationCheckerInterface $authorization,
        TeamManager $teamManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->authorization = $authorization;
        $this->teamManager = $teamManager;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/team/index",
     *     name="claro_team_index"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param Workspace $workspace
     * @param User      $user
     */
    public function indexAction(Workspace $workspace, User $user)
    {
        $isWorkspaceManager = $this->isWorkspaceManager($workspace, $user);
        $params = [];

        //display the correct view when impersonnating
        $impersonating = $this->authorization->isGranted('ROLE_USURPATE_WORKSPACE_ROLE');
        $impersonatingCollaborator = false;
        foreach ($this->get('security.token_storage')->getToken()->getRoles() as $role) {
            if ($role->getRole() === 'ROLE_WS_COLLABORATOR_'.$workspace->getGuid()) {
                $impersonatingCollaborator = true;
            }
        }

        if ($isWorkspaceManager && !($impersonating && $impersonatingCollaborator)) {
            $params['_controller'] = 'ClarolineTeamBundle:Team:managerMenu';
            $params['workspace'] = $workspace->getId();
        } else {
            $params['_controller'] = 'ClarolineTeamBundle:Team:userMenu';
            $params['workspace'] = $workspace->getId();
        }
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/team/manager/menu/ordered/by/{orderedBy}/order/{order}",
     *     name="claro_team_manager_menu",
     *     defaults={"orderedBy"="name","order"="ASC"}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Workspace $workspace
     * @param User      $user
     */
    public function managerMenuAction(Workspace $workspace, User $user, $orderedBy = 'name', $order = 'ASC')
    {
        $this->checkWorkspaceManager($workspace, $user);
        $params = $this->teamManager->getParametersByWorkspace($workspace);

        if (is_null($params)) {
            $params = $this->teamManager->createWorkspaceTeamParameters($workspace);
        }
        $teams = $this->teamManager->getTeamsByWorkspace($workspace, $orderedBy, $order);
        $teamsWithUsers = $this->teamManager->getTeamsWithUsersByWorkspace($workspace);
        $nbUsers = [];

        foreach ($teamsWithUsers as $teamWithUsers) {
            $nbUsers[$teamWithUsers['team']->getId()] = $teamWithUsers['nb_users'];
        }

        return [
            'workspace' => $workspace,
            'user' => $user,
            'teams' => $teams,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'nbUsers' => $nbUsers,
            'params' => $params,
        ];
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/team/user/menu/ordered/by/{orderedBy}/order/{order}",
     *     name="claro_team_user_menu",
     *     defaults={"orderedBy"="name","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Workspace $workspace
     * @param User      $user
     */
    public function userMenuAction(Workspace $workspace, User $user, $orderedBy = 'name', $order = 'ASC')
    {
        $this->checkToolAccess($workspace);
        $params = $this->teamManager->getParametersByWorkspace($workspace);

        if (is_null($params)) {
            $params = $this->teamManager->createWorkspaceTeamParameters($workspace);
        }
        $userTeams = $this->teamManager->getTeamsByUserAndWorkspace($user, $workspace);
        $teams = $this->teamManager->getTeamsWithExclusionsByWorkspace(
            $workspace,
            $userTeams,
            $orderedBy,
            $order
        );
        $teamsWithUsers = $this->teamManager->getTeamsWithUsersByWorkspace($workspace);
        $nbUsers = [];

        foreach ($teamsWithUsers as $teamWithUsers) {
            $nbUsers[$teamWithUsers['team']->getId()] = $teamWithUsers['nb_users'];
        }

        return [
            'workspace' => $workspace,
            'user' => $user,
            'userTeams' => $userTeams,
            'teams' => $teams,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'nbUsers' => $nbUsers,
            'params' => $params,
            'nbTeams' => count($userTeams),
        ];
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/team/create/form",
     *     name="claro_team_create_form"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teamCreateFormAction(Workspace $workspace, User $user)
    {
        $this->checkWorkspaceManager($workspace, $user);
        $params = $this->teamManager->getParametersByWorkspace($workspace);
        $team = new Team();
        $team->setIsPublic($params->getIsPublic());
        $team->setSelfRegistration($params->getSelfRegistration());
        $team->setSelfUnregistration($params->getSelfUnregistration());
        $form = $this->formFactory->create(new TeamType(), $team);

        return ['form' => $form->createView(), 'workspace' => $workspace];
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/team/create",
     *     name="claro_team_create"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTeamBundle:Team:teamCreateForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teamCreateAction(Workspace $workspace, User $user)
    {
        $this->checkWorkspaceManager($workspace, $user);
        $team = new Team();
        $form = $this->formFactory->create(new TeamType(), $team);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $resource = $form->get('defaultResource')->getData();
            $creatableResources = $form->get('resourceTypes')->getData();
            $this->teamManager->createTeam($team, $workspace, $user, $resource, $creatableResources->toArray());
            $this->teamManager->initializeTeamRights($team);

            return new RedirectResponse(
                $this->router->generate('claro_team_manager_menu', ['workspace' => $workspace->getId()])
            );
        } else {
            return ['form' => $form->createView(), 'workspace' => $workspace];
        }
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/edit/form",
     *     name="claro_team_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teamEditFormAction(Team $team, User $user)
    {
        $workspace = $team->getWorkspace();
        $isWorkspaceManager = $this->isWorkspaceManager($workspace, $user);
        $isTeamManager = $this->isTeamManager($team, $user);

        if (!$isWorkspaceManager && !$isTeamManager) {
            throw new AccessDeniedException();
        }
        $form = $this->formFactory->create(new TeamEditType(), $team);

        return ['form' => $form->createView(), 'team' => $team, 'workspace' => $workspace];
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/edit",
     *     name="claro_team_edit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTeamBundle:Team:teamEditForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teamEditAction(Team $team, User $user)
    {
        $workspace = $team->getWorkspace();
        $isWorkspaceManager = $this->isWorkspaceManager($workspace, $user);
        $isTeamManager = $this->isTeamManager($team, $user);

        if (!$isWorkspaceManager && !$isTeamManager) {
            throw new AccessDeniedException();
        }
        $oldIsPublic = $team->getIsPublic();
        $form = $this->formFactory->create(new TeamEditType(), $team);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $newIsPublic = $team->getIsPublic() || $team->getIsPublic() === 1;
            $this->teamManager->persistTeam($team);

            if ($oldIsPublic !== $newIsPublic) {
                $this->teamManager->initializeTeamDirectoryPerms($team);
            }

            return new JsonResponse('success', 200);
        } else {
            return ['form' => $form->createView(), 'team' => $team, 'workspace' => $workspace];
        }
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/delete/{withDirectory}",
     *     name="claro_team_delete",
     *     defaults={"withDirectory"=0},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teamDeleteAction(Team $team, User $user, $withDirectory = 0)
    {
        $workspace = $team->getWorkspace();
        $this->checkWorkspaceManager($workspace, $user);
        $deleteDirectory = (intval($withDirectory) === 1);
        $this->teamManager->deleteTeam($team, $deleteDirectory);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/multiple/teams/create/form",
     *     name="claro_team_multiple_create_form"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function multipleTeamsCreateFormAction(Workspace $workspace, User $user)
    {
        $this->checkWorkspaceManager($workspace, $user);
        $params = $this->teamManager->getParametersByWorkspace($workspace);
        $form = $this->formFactory->create(new MultipleTeamsType($params));

        return ['form' => $form->createView(), 'workspace' => $workspace];
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/multiple/teams/create",
     *     name="claro_team_multiple_create"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTeamBundle:Team:multipleTeamsCreateForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function multipleTeamCreateAction(Workspace $workspace, User $user)
    {
        $this->checkWorkspaceManager($workspace, $user);
        $params = $this->teamManager->getParametersByWorkspace($workspace);
        $form = $this->formFactory->create(new MultipleTeamsType($params));
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $datas = $form->getData();
            $this->teamManager->createMultipleTeams(
                $workspace,
                $user,
                $datas['name'],
                $datas['nbTeams'],
                $datas['description'],
                $datas['maxUsers'],
                $datas['isPublic'],
                $datas['selfRegistration'],
                $datas['selfUnregistration'],
                $form->get('defaultResource')->getData(),
                $form->get('resourceTypes')->getData()->toArray()
            );

            return new RedirectResponse(
                $this->router->generate('claro_team_manager_menu', ['workspace' => $workspace->getId()])
            );
        } else {
            return ['form' => $form->createView(), 'workspace' => $workspace];
        }
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/user/{user}/register",
     *     name="claro_team_manager_register_user",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("manager", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managerRegisterUserToTeamAction(Team $team, User $user, User $manager)
    {
        $workspace = $team->getWorkspace();
        $this->checkWorkspaceManager($workspace, $manager);
        $params = $this->teamManager->getParametersByWorkspace($workspace);
        $maxUsers = $team->getMaxUsers();
        $full = !is_null($maxUsers) && (count($team->getUsers()) >= $maxUsers);
        $nbAllowedTeams = $params->getMaxTeams();
        $userTeams = $this->teamManager->getTeamsByUserAndWorkspace($user, $workspace);
        $nbTeams = count($userTeams);
        $nbAllowed = is_null($nbAllowedTeams) || ($nbTeams < $nbAllowedTeams);

        if (!$full && $nbAllowed) {
            $this->teamManager->registerUserToTeam($team, $user);
        }

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/user/{user}/unregister",
     *     name="claro_team_manager_unregister_user",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("manager", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managerUnregisterUserFromTeamAction(Team $team, User $user, User $manager)
    {
        $workspace = $team->getWorkspace();
        $isWorkspaceManager = $this->isWorkspaceManager($workspace, $manager);
        $isTeamManager = $this->isTeamManager($team, $manager);

        if (!$isWorkspaceManager && !$isTeamManager) {
            throw new AccessDeniedException();
        }
        $this->teamManager->unregisterUserFromTeam($team, $user);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/users/register",
     *     name="claro_team_manager_register_users",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("manager", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "users"}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managerRegisterUsersToTeamAction(Team $team, array $users, User $manager)
    {
        $this->checkWorkspaceManager($team->getWorkspace(), $manager);
        $this->teamManager->registerUsersToTeam($team, $users);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/users/unregister",
     *     name="claro_team_manager_unregister_users",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("manager", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "users"}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managerUnregisterUsersFromTeamAction(Team $team, array $users, User $manager)
    {
        $this->checkWorkspaceManager($team->getWorkspace(), $manager);
        $this->teamManager->unregisterUsersFromTeam($team, $users);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/teams/delete/{withDirectory}",
     *     name="claro_team_manager_delete_teams",
     *     defaults={"withDirectory"=0},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("manager", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "teams",
     *      class="ClarolineTeamBundle:Team",
     *      options={"multipleIds" = true, "name" = "teams"}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managerDeleteTeamsAction(Workspace $workspace, array $teams, User $manager, $withDirectory = 0)
    {
        $this->checkWorkspaceManager($workspace, $manager);
        $deleteDirectory = (intval($withDirectory) === 1);
        $this->teamManager->deleteTeams($teams, $deleteDirectory);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/teams/empty",
     *     name="claro_team_manager_empty_teams",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("manager", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "teams",
     *      class="ClarolineTeamBundle:Team",
     *      options={"multipleIds" = true, "name" = "teams"}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managerEmtyTeamsAction(Workspace $workspace, array $teams, User $manager)
    {
        $this->checkWorkspaceManager($workspace, $manager);
        $this->teamManager->emptyTeams($teams);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/teams/fill",
     *     name="claro_team_manager_fill_teams",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("manager", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "teams",
     *      class="ClarolineTeamBundle:Team",
     *      options={"multipleIds" = true, "name" = "teams"}
     * )
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managerFillTeamsAction(Workspace $workspace, array $teams, User $manager)
    {
        $this->checkWorkspaceManager($workspace, $manager);
        $this->teamManager->fillTeams($workspace, $teams);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/self/register/user",
     *     name="claro_team_self_register_user",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selfRegisterUserToTeamAction(Team $team, User $user)
    {
        $workspace = $team->getWorkspace();
        $this->checkToolAccess($workspace);
        $params = $this->teamManager->getParametersByWorkspace($workspace);
        $maxUsers = $team->getMaxUsers();
        $full = !is_null($maxUsers) && (count($team->getUsers()) >= $maxUsers);
        $nbAllowedTeams = $params->getMaxTeams();
        $userTeams = $this->teamManager->getTeamsByUserAndWorkspace($user, $workspace);
        $nbTeams = count($userTeams);
        $nbAllowed = is_null($nbAllowedTeams) || ($nbTeams < $nbAllowedTeams);

        if ($team->getSelfRegistration() && !$full && $nbAllowed) {
            $this->teamManager->registerUserToTeam($team, $user);
            $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
            $this->tokenStorage->setToken($token);
        }

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/self/unregister/user",
     *     name="claro_team_self_unregister_user",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selfUnregisterUserToTeamAction(Team $team, User $user)
    {
        $workspace = $team->getWorkspace();
        $this->checkToolAccess($workspace);

        if ($team->getSelfUnregistration()) {
            $this->teamManager->unregisterUserFromTeam($team, $user);
        }

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/workspace/team/parameters/{params}/edit/form",
     *     name="claro_team_parameters_edit_form"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teamParamsEditFormAction(WorkspaceTeamParameters $params, User $user)
    {
        $workspace = $params->getWorkspace();
        $this->checkWorkspaceManager($workspace, $user);
        $form = $this->formFactory->create(new TeamParamsType(), $params);

        return ['form' => $form->createView(), 'params' => $params, 'workspace' => $workspace];
    }

    /**
     * @EXT\Route(
     *     "/workspace/team/parameters/{params}/edit",
     *     name="claro_team_parameters_edit"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTeamBundle:Team:teamParamsEditForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teamParamsEditAction(WorkspaceTeamParameters $params, User $user)
    {
        $workspace = $params->getWorkspace();
        $this->checkWorkspaceManager($workspace, $user);
        $form = $this->formFactory->create(new TeamParamsType(), $params);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->teamManager->persistWorkspaceTeamParameters($params);

            return new RedirectResponse(
                $this->router->generate('claro_team_manager_menu', ['workspace' => $workspace->getId()])
            );
        } else {
            return ['form' => $form->createView(), 'params' => $params, 'workspace' => $workspace];
        }
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/registration/users/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_team_registration_users_list",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="firstName","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @EXT\Template()
     *
     * Displays the list of users who are registered to the workspace.
     *
     * @param Team   $team
     * @param string $search
     * @param int    $page
     * @param int    $max
     * @param string $orderedBy
     * @param string $order
     */
    public function registrationUserslistAction(
        Team $team,
        User $user,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'firstName',
        $order = 'ASC'
    ) {
        $workspace = $team->getWorkspace();
        $this->checkWorkspaceManager($workspace, $user);

        $users = $search === '' ?
            $this->teamManager->getWorkspaceUsers($workspace, $orderedBy, $order, $page, $max) :
            $this->teamManager->getSearchedWorkspaceUsers($workspace, $search, $orderedBy, $order, $page, $max);
        $params = $this->teamManager->getParametersByWorkspace($workspace);
        $usersArray = [];
        $nbTeams = [];

        foreach ($users as $u) {
            $usersArray[] = $u;
        }
        $usersNbTeams = $this->teamManager->getNbTeamsByUsers($workspace, $usersArray);

        foreach ($usersNbTeams as $userNbTeams) {
            $nbTeams[$userNbTeams['user_id']] = $userNbTeams['nb_teams'];
        }
        $registered = [];

        foreach ($team->getUsers() as $user) {
            $registered[$user->getId()] = $user;
        }

        return [
            'workspace' => $workspace,
            'team' => $team,
            'users' => $users,
            'search' => $search,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'registered' => $registered,
            'nbTeams' => $nbTeams,
            'params' => $params,
        ];
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/registration/unregistered/users/list/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_team_registration_unregistered_users_list",
     *     defaults={"page"=1, "search"="", "max"=50, "orderedBy"="firstName","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @EXT\Template()
     *
     * Displays the list of users who are registered to the workspace.
     *
     * @param Team   $team
     * @param string $search
     * @param int    $page
     * @param int    $max
     * @param string $orderedBy
     * @param string $order
     */
    public function registrationUnregisteredUserslistAction(
        Team $team,
        User $user,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'firstName',
        $order = 'ASC'
    ) {
        $workspace = $team->getWorkspace();
        $this->checkWorkspaceManager($workspace, $user);

        $users = $search === '' ?
            $this->teamManager->getUnregisteredUsersByTeam($team, $orderedBy, $order, $page, $max) :
            $this->teamManager->getSearchedUnregisteredUsersByTeam($team, $search, $orderedBy, $order, $page, $max);
        $params = $this->teamManager->getParametersByWorkspace($workspace);
        $usersArray = [];
        $nbTeams = [];

        foreach ($users as $u) {
            $usersArray[] = $u;
        }
        $usersNbTeams = $this->teamManager->getNbTeamsByUsers($workspace, $usersArray);

        foreach ($usersNbTeams as $userNbTeams) {
            $nbTeams[$userNbTeams['user_id']] = $userNbTeams['nb_teams'];
        }

        return [
            'workspace' => $workspace,
            'team' => $team,
            'users' => $users,
            'search' => $search,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'params' => $params,
            'nbTeams' => $nbTeams,
        ];
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/users/list",
     *     name="claro_team_users_list",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @EXT\Template()
     *
     * Displays the list of users registered in a team
     *
     * @param Team $team
     */
    public function teamUserslistAction(Team $team)
    {
        $this->checkToolAccess($team->getWorkspace());
        $users = $team->getUsers();

        return ['team' => $team, 'users' => $users];
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/description/display",
     *     name="claro_team_display_description",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @EXT\Template()
     *
     * Displays the description of a team
     *
     * @param Team $team
     */
    public function teamDescriptionDisplayAction(Team $team)
    {
        $this->checkToolAccess($team->getWorkspace());

        return ['description' => $team->getDescription()];
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/manager/index",
     *     name="claro_team_manager_team_index"
     * )
     * @EXT\ParamConverter("manager", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Team $team
     * @param User $user
     */
    public function managerTeamIndexAction(Team $team, User $manager)
    {
        $workspace = $team->getWorkspace();
        $this->checkWorkspaceManager($workspace, $manager);
        $users = $team->getUsers();
        $params = $this->teamManager->getParametersByWorkspace($workspace);

        return ['workspace' => $workspace, 'users' => $users, 'team' => $team, 'params' => $params];
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/user/index",
     *     name="claro_team_user_team_index"
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Team $team
     * @param User $currentUser
     */
    public function userTeamIndexAction(Team $team, User $currentUser)
    {
        $workspace = $team->getWorkspace();
        $this->checkToolAccess($workspace);
        $isTeamMember = $this->isTeamMember($team, $currentUser);
        $isTeamManager = $this->isTeamManager($team, $currentUser);

        if (!$isTeamMember && !$isTeamManager) {
            throw new AccessDeniedException();
        }
        $users = $team->getUsers();
        $params = $this->teamManager->getParametersByWorkspace($workspace);

        return [
            'workspace' => $workspace,
            'currentUser' => $currentUser,
            'users' => $users,
            'team' => $team,
            'params' => $params,
            'isTeamMember' => $isTeamMember,
            'isTeamManager' => $isTeamManager,
        ];
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/user/{user}/register/manager",
     *     name="claro_team_manager_register_manager",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("manager", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managerRegisterManagerToTeamAction(Team $team, User $user, User $manager)
    {
        $workspace = $team->getWorkspace();
        $this->checkWorkspaceManager($workspace, $manager);
        $this->teamManager->registerManagerToTeam($team, $user);

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/unregister/manager",
     *     name="claro_team_manager_unregister_manager",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("manager", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managerUnregisterManagerFromTeamAction(Team $team, User $manager)
    {
        $this->checkWorkspaceManager($team->getWorkspace(), $manager);
        $this->teamManager->unregisterManagerFromTeam($team);

        return new Response('success', 200);
    }

    private function checkToolAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('claroline_team_tool', $workspace)) {
            throw new AccessDeniedException();
        }
    }

    private function checkWorkspaceManager(Workspace $workspace, User $user)
    {
        if (!$this->isWorkspaceManager($workspace, $user)) {
            throw new AccessDeniedException();
        }
    }

    private function isWorkspaceManager(Workspace $workspace, User $user)
    {
        $isWorkspaceManager = false;
        $managerRole = 'ROLE_WS_MANAGER_'.$workspace->getGuid();
        $roleNames = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roleNames) || in_array($managerRole, $roleNames)) {
            $isWorkspaceManager = true;
        }

        return $isWorkspaceManager;
    }

    private function isTeamMember(Team $team, User $user)
    {
        $users = $team->getUsersArrayCollection();

        return $users->contains($user);
    }

    private function isTeamManager(Team $team, User $user)
    {
        return $user === $team->getTeamManager();
    }
}

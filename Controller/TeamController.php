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
use Claroline\TeamBundle\Form\MultipleTeamsType;
use Claroline\TeamBundle\Form\TeamType;
use Claroline\TeamBundle\Manager\TeamManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TeamController extends Controller
{
    private $formFactory;
    private $httpKernel;
    private $request;
    private $router;
    private $securityContext;
    private $teamManager;

    /**
     * @DI\InjectParams({
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "httpKernel"      = @DI\Inject("http_kernel"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "router"          = @DI\Inject("router"),
     *     "securityContext" = @DI\Inject("security.context"),
     *     "teamManager"     = @DI\Inject("claroline.manager.team_manager")
     * })
     */
    public function __construct(
        FormFactory $formFactory,
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        SecurityContextInterface $securityContext,
        TeamManager $teamManager
    )
    {
        $this->formFactory = $formFactory;
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->securityContext = $securityContext;
        $this->teamManager = $teamManager;
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/team/index",
     *     name="claro_team_index"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @param Workspace $workspace
     * @param User $user
     */
    public function indexAction(Workspace $workspace, User $user)
    {
        $isWorkspaceManager = $this->isWorkspaceManager($workspace, $user);
        $params = array();

        if ($isWorkspaceManager) {
            $params['_controller'] = 'ClarolineTeamBundle:Team:managerMenu';
            $params['workspace'] = $workspace->getId();
        } else {
            $params['_controller'] = 'ClarolineTeamBundle:Team:userMenu';
            $params['workspace'] = $workspace->getId();
        }
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);

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
     * @param User $user
     */
    public function managerMenuAction(
        Workspace $workspace,
        User $user,
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        $this->checkWorkspaceManager($workspace, $user);

        $teams = $this->teamManager
            ->getTeamsByWorkspace($workspace, $orderedBy, $order);

        return array(
            'workspace' => $workspace,
            'user' => $user,
            'teams' => $teams,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/{workspace}/team/user/menu",
     *     name="claro_team_user_menu",
     *     defaults={"orderedBy"="name","order"="ASC"}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @param Workspace $workspace
     * @param User $user
     */
    public function userMenuAction(
        Workspace $workspace,
        User $user,
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        $this->checkToolAccess($workspace);

        $teams = $this->teamManager
            ->getTeamsByWorkspace($workspace, $orderedBy, $order);

        return array(
            'workspace' => $workspace,
            'user' => $user,
            'teams' => $teams
        );
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
        $form = $this->formFactory->create(new TeamType(), new Team());

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
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
            $this->teamManager->createTeam($team, $workspace, $user);
            $this->teamManager->initializeTeamRights($team);

            return new RedirectResponse(
                $this->router->generate(
                    'claro_team_manager_menu',
                    array('workspace' => $workspace->getId())
                )
            );
        } else {

            return array(
                'form' => $form->createView(),
                'workspace' => $workspace
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/edit/form",
     *     name="claro_team_edit_form"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teamEditFormAction(Team $team, User $user)
    {
        $workspace = $team->getWorkspace();
        $this->checkWorkspaceManager($workspace, $user);
        $form = $this->formFactory->create(new TeamType(), $team);

        return array(
            'form' => $form->createView(),
            'team' => $team,
            'workspace' => $workspace
        );
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/edit",
     *     name="claro_team_edit"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineTeamBundle:Team:teamEditForm.html.twig")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teamEditAction(Team $team, User $user)
    {
        $workspace = $team->getWorkspace();
        $this->checkWorkspaceManager($workspace, $user);
        $form = $this->formFactory->create(new TeamType(), $team);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->teamManager->persistTeam($team);

            return new RedirectResponse(
                $this->router->generate(
                    'claro_team_manager_menu',
                    array('workspace' => $workspace->getId())
                )
            );
        } else {

            return array(
                'form' => $form->createView(),
                'team' => $team,
                'workspace' => $workspace
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/team/{team}/delete",
     *     name="claro_team_delete",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function teamDeleteAction(Team $team, User $user)
    {
        $workspace = $team->getWorkspace();
        $this->checkWorkspaceManager($workspace, $user);
        $this->teamManager->deleteTeam($team);

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
        $form = $this->formFactory->create(new MultipleTeamsType());

        return array(
            'form' => $form->createView(),
            'workspace' => $workspace
        );
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
        $form = $this->formFactory->create(new MultipleTeamsType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $datas = $form->getData();
            $this->teamManager->createMultipleTeams(
                $workspace,
                $user,
                $datas['name'],
                $datas['nbTeams'],
                $datas['maxUsers'],
                $datas['isPublic'],
                $datas['selfRegistration'],
                $datas['selfUnregistration']
            );
//            throw new \Exception(var_dump($datas));
//            $this->teamManager->createTeam($team, $workspace, $user);
//            $this->teamManager->initializeTeamRights($team);

            return new RedirectResponse(
                $this->router->generate(
                    'claro_team_manager_menu',
                    array('workspace' => $workspace->getId())
                )
            );
        } else {

            return array(
                'form' => $form->createView(),
                'workspace' => $workspace
            );
        }
    }

    private function checkToolAccess(Workspace $workspace)
    {
        if (!$this->securityContext->isGranted('claroline_team_tool', $workspace)) {

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
        $managerRole = 'ROLE_WS_MANAGER_' . $workspace->getGuid();
        $roleNames = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roleNames) ||
            in_array($managerRole, $roleNames)) {

            $isWorkspaceManager = true;
        }

        return $isWorkspaceManager;
    }
}

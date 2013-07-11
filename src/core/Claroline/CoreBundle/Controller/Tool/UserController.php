<?php

namespace Claroline\CoreBundle\Controller\Tool;

use LogicException;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Event\Event\Log\LogWorkspaceRoleSubscribeEvent;
use Claroline\CoreBundle\Event\Event\Log\LogWorkspaceRoleUnsubscribeEvent;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Pager\PagerFactory;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class UserController extends Controller
{
    const NUMBER_USER_PER_ITERATION = 25;

    private $userManager;
    private $roleManager;
    private $eventDispatcher;
    private $pagerFactory;
    private $security;
    private $router;

    /**
     * @DI\InjectParams({
     *     "userManager"        = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "eventDispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "pagerFactory"       = @DI\Inject("claroline.pager.pager_factory"),
     *     "security"           = @DI\Inject("security.context"),
     *     "router"             = @DI\Inject("router")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        StrictDispatcher $eventDispatcher,
        PagerFactory $pagerFactory,
        SecurityContextInterface $security,
        UrlGeneratorInterface $router
    )
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->pagerFactory = $pagerFactory;
        $this->security = $security;
        $this->router = $router;
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/users/registered/page/{page}",
     *     name="claro_workspace_registered_user_list",
     *     defaults={"page"=1, "search"=""},
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\Route(
     *     "/{workspaceId}/users/registered/page/{page}/search/{search}",
     *     name="claro_workspace_registered_user_list_search",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\user_management:registeredUsers.html.twig")
     */
    public function registeredUsersListAction(AbstractWorkspace $workspace, $page, $search)
    {
        $this->checkRegistration($workspace);
        $pager = $search === '' ?
            $this->userManager->getUsersByWorkspace($workspace, $page) :
            $this->userManager->getUsersByWorkspaceAndName($workspace, $search, $page);

        return array('workspace' => $workspace, 'pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/users/unregistered/page/{page}",
     *     name="claro_workspace_unregistered_user_list",
     *     defaults={"page"=1, "search"=""},
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\Route(
     *     "/{workspaceId}/users/unregistered/page/{page}/search/{search}",
     *     name="claro_workspace_unregistered_user_list_search",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\user_management:unregisteredUsers.html.twig")
     */
    public function unregiseredUsersListAction(AbstractWorkspace $workspace, $page, $search)
    {
        $this->checkRegistration($workspace, false);
        $pager = $search === '' ?
            $this->userManager->getWorkspaceOutsiders($workspace, $page) :
            $this->userManager->getWorkspaceOutsidersByName($workspace, $search, $page);

        return array(
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/user/{userId}",
     *     name="claro_workspace_tools_show_user_parameters",
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$", "userId"="^(?=.*[1-9].*$)\d*$" },
     *     options={"expose"=true}
     * )
     *
     * @EXT\Route(
     *     "/{workspaceId}/user/{userId}",
     *     name="claro_workspace_tools_edit_user_parameters",
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$", "userId"="^(?=.*[1-9].*$)\d*$" },
     *     options={"expose"=true}
     * )
     * @EXT\Method({"POST", "GET"})
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool\workspace\user_management:userParameters.html.twig")
     *
     * Renders the user parameter page with its layout and
     * edit the user parameters for the selected workspace.
     *
     * @param integer $workspaceId the workspace id
     * @param integer $userId     the user id
     *
     * @return Response
     */
    public function userParametersAction(AbstractWorkspace $workspace, User $user)
    {
        $this->checkRegistration($workspace, false);
        $role = $this->roleManager->getWorkspaceRoleForUser($user, $workspace);
        $defaultData = array('role' => $role);
        $workspaceId = $workspace->getId();
        $form = $this->createFormBuilder($defaultData, array('translation_domain' => 'platform'))
            ->add(
                'role',
                'entity',
                array(
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'property' => 'translationKey',
                    'query_builder' => function (EntityRepository $er) use ($workspaceId) {
                        return $er->createQueryBuilder('wr')
                            ->select('role')
                            ->from('Claroline\CoreBundle\Entity\Role', 'role')
                            ->leftJoin('role.workspace', 'workspace')
                            ->where('workspace.id = :workspaceId')
                            ->andWhere("role.name != 'ROLE_ANONYMOUS'")
                            ->setParameter('workspaceId', $workspaceId);
                    }
                )
            )
            ->getForm();

        if ($this->getRequest()->getMethod() === 'POST') {
            $request = $this->getRequest();
            $parameters = $request->request->all();
            //cannot bind request: why ?
            $newRole = $this->roleManager->getRoleById($parameters['form']['role']);

            if ($newRole->getId() != $this->roleManager->getManagerRole($workspace)->getId()) {
                $this->checkRemoveManagerRoleIsValid(array($user), $workspace);
            }

            $this->roleManager->dissociateRole($user, $role);
            $this->roleManager->associateRole($user, $newRole);
            $route = $this->router->generate(
                'claro_workspace_open_tool',
                array('workspaceId' => $workspaceId, 'toolName' => 'user_management')
            );
            $this->eventDispatcher->dispatch(
                'log',
                'Log\LogWorkspaceRoleUnsubscribe',
                array($role, $user)
            );
            $this->eventDispatcher->dispatch(
                'log',
                'Log\LogWorkspaceRoleSubscribe',
                array($newRole, $user)
            );

            return new RedirectResponse($route);
        }

        return array(
            'workspace' => $workspace,
            'user' => $user,
            'form' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/add/user",
     *     name="claro_workspace_multiadd_user",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method("PUT")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true}
     * )
     *
     * Adds many users to a workspace.
     * It uses a query string of userIds as parameter (ids[]=1&ids[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function addUsersAction(AbstractWorkspace $workspace, array $users)
    {
        $this->checkRegistration($workspace, false);
        $role = $this->roleManager->getCollaboratorRole($workspace);

        foreach ($users as $user) {
            $userRole = $this->roleManager->getWorkspaceRoleForUser($user, $workspace);

            if (is_null($userRole)) {
                $this->roleManager->associateRole($user, $role);
                $log = new LogWorkspaceRoleSubscribeEvent($role, $user);
                $this->eventDispatcher->dispatch('log', $log);
            }
        }

        return new JsonResponse($this->userManager->convertUsersToArray($users));
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/users",
     *     name="claro_workspace_delete_users",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true}
     * )
     *
     * Removes many users from a workspace.
     * It uses a query string of groupIds as parameter (ids[]=1&ids[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function removeUsersAction(AbstractWorkspace $workspace, array $users)
    {
        $this->checkRegistration($workspace, false);
        $roles = $this->roleManager->getRolesByWorkspace($workspace);
        $this->checkRemoveManagerRoleIsValid($users, $workspace);

        foreach ($users as $user) {
            foreach ($roles as $role) {
                if ($user->hasRole($role->getName())) {
                    $this->roleManager->dissociateRole($user, $role);

                    $log = new LogWorkspaceRoleUnsubscribeEvent($role, $user);
                    $this->eventDispatcher->dispatch('log', $log);
                }
            }
        }

        return new Response("success", 204);
    }

    /**
     * Checks if the role manager of the user can be changed.
     * There should be awlays at least one manager of a workspace.
     *
     * @param array $userIds an array of user ids
     * @param AbstractWorkspace $workspace the relevant workspace
     *
     * @throws LogicException
     */
    private function checkRemoveManagerRoleIsValid(array $users, AbstractWorkspace $workspace)
    {
        $countRemovedManagers = 0;
        $managerRole = $this->roleManager->getManagerRole($workspace);

        foreach ($users as $user) {
            if ($workspace == $user->getPersonalWorkspace()) {
                throw new LogicException("You can't remove the original manager from a personal workspace");
            }
            if ($user->hasRole($managerRole->getName())) {
                $countRemovedManagers++;
            }
        }

        $userManagers = $this->userManager->getUserByWorkspaceAndRole($workspace, $managerRole);
        $countUserManagers = count($userManagers);

        if ($countRemovedManagers >= $countUserManagers) {
            throw new LogicException(
                "You can't remove every managers (you're trying to remove {$countRemovedManagers} "
                . "manager(s) out of {$countUserManagers})"
            );
        }
    }

    /**
     * Checks if the current user has access to the user management tool.
     *
     * @param AbstractWorkspace $workspace
     *
     * @throws AccessDeniedException
     */
    private function checkRegistration(AbstractWorkspace $workspace, $allowAnonymous = true)
    {
        if (($this->security->getToken()->getUser() === 'anon.' && !$allowAnonymous)
            || !$this->security->isGranted('user_management', $workspace)) {
            throw new AccessDeniedException();
        }
    }
}

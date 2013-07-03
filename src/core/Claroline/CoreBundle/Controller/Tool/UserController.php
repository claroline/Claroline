<?php

namespace Claroline\CoreBundle\Controller\Tool;

use LogicException;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleSubscribeEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleUnsubscribeEvent;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class UserController extends Controller
{
    const ABSTRACT_WS_CLASS = 'ClarolineCoreBundle:Workspace\AbstractWorkspace';
    const NUMBER_USER_PER_ITERATION = 25;

    private $userManager;
    private $roleManager;

    /**
     * @DI\InjectParams({
     *     "userManager"    = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"    = @DI\Inject("claroline.manager.role_manager")
     * })
     */
    public function __construct(UserManager $userManager, RoleManager $roleManager)
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
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
        $query = ($search == "") ?
            $this->userManager->getUsersByWorkspace($workspace, true) :
            $this->userManager->getUsersByWorkspaceAndName($workspace, $search, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

        return array(
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search
        );
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
        $query = ($search == "") ?
            $this->userManager->getWorkspaceOutsiders($workspace, true) :
            $this->userManager->getWorkspaceOutsidersByName($workspace, $search, true);
        $adapter = new DoctrineORMAdapter($query);
        $pager = new Pagerfanta($adapter);
        $pager->setMaxPerPage(20);
        $pager->setCurrentPage($page);

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
        $em = $this->get('doctrine.orm.entity_manager');
        $this->checkRegistration($workspace, false);
        $roleRepo = $em->getRepository('ClarolineCoreBundle:Role');
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
            $newRole = $roleRepo->find($parameters['form']['role']);

            if ($newRole->getId() != $this->roleManager->getManagerRole($workspace)->getId()) {
                $this->checkRemoveManagerRoleIsValid(array($user->getId()), $workspace);
            }

            $this->roleManager->dissociateRole($user, $role);
            $this->roleManager->associateRole($user, $newRole);
            $route = $this->get('router')->generate(
                'claro_workspace_open_tool',
                array('workspaceId' => $workspaceId, 'toolName' => 'user_management')
            );

            $log = new LogWorkspaceRoleUnsubscribeEvent($role, $user);
            $this->get('event_dispatcher')->dispatch('log', $log);

            $log = new LogWorkspaceRoleSubscribeEvent($newRole, $user);
            $this->get('event_dispatcher')->dispatch('log', $log);
            $em->flush();

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
     *
     * Adds many users to a workspace.
     * It uses a query string of userIds as parameter (userIds[]=1&userIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function addUsersAction(AbstractWorkspace $workspace)
    {
        $params = $this->get('request')->query->all();
        $users = array();
        $em = $this->get('doctrine.orm.entity_manager');
        $this->checkRegistration($workspace, false);
        $roleRepo = $em->getRepository('ClarolineCoreBundle:Role');
        $role = $roleRepo->findCollaboratorRole($workspace);

        if (isset($params['ids'])) {

            foreach ($params['ids'] as $userId) {
                $user = $em->find('ClarolineCoreBundle:User', $userId);
                //We only add the role if the user isn't already registered.
                $userRole = $roleRepo->findWorkspaceRoleForUser($user, $workspace);
                if ($userRole === null) {
                    $users[] = $user;
                    $this->roleManager->associateRole($user, $role);
                }
            }
        }

        foreach ($users as $user) {
            $log = new LogWorkspaceRoleSubscribeEvent($role, $user);
            $this->get('event_dispatcher')->dispatch('log', $log);
        }

        $response = new Response($this->get('claroline.resource.converter')->jsonEncodeUsers($users));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
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
     *
     * Removes many users from a workspace.
     * It uses a query string of groupIds as parameter (userIds[]=1&userIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function removeUsersAction(AbstractWorkspace $workspace)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $roleManager = $this->get('claroline.manager.role_manager');
        $this->checkRegistration($workspace, false);
        $roles = $em->getRepository('ClarolineCoreBundle:Role')
            ->findByWorkspace($workspace);
        $params = $this->get('request')->query->all();

        $users = array();
        $rolesForUsers = array();
        if (isset($params['ids'])) {
            $this->checkRemoveManagerRoleIsValid($params['ids'], $workspace);
            foreach ($params['ids'] as $userId) {

                $user = $em->find('ClarolineCoreBundle:User', $userId);

                if (null != $user) {
                    $rolesForUser = array();
                    foreach ($roles as $role) {
                        if ($user->hasRole($role->getName())) {
                            $roleManager->dissociateRole($user, $role);
                            $rolesForUser[] = $role;
                        }
                    }
                    $users[] = $user;
                    $rolesForUsers['user_'.$user->getId()] = $rolesForUser;
                }
            }
        }

        foreach ($users as $user) {
            foreach ($rolesForUsers['user_'.$user->getId()] as $role) {
                $log = new LogWorkspaceRoleUnsubscribeEvent($role, $user);
                $this->get('event_dispatcher')->dispatch('log', $log);
            }
        }

        $em->flush();

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
    private function checkRemoveManagerRoleIsValid(array $userIds, AbstractWorkspace $workspace)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $countRemovedManagers = 0;
        $managerRole = $em->getRepository('ClarolineCoreBundle:Role')
            ->findManagerRole($workspace);

        foreach ($userIds as $userId) {
            $user = $em->find('ClarolineCoreBundle:User', $userId);

            if (null !== $user) {
                if ($workspace == $user->getPersonalWorkspace()) {
                    throw new LogicException("You can't remove the original manager from a personal workspace");
                }
                if ($user->hasRole($managerRole->getName())) {
                    $countRemovedManagers++;
                }
            }
        }

        $userManagers = $em->getRepository('ClarolineCoreBundle:User')
            ->findByWorkspaceAndRole($workspace, $managerRole);
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
        $security = $this->get('security.context');

        if (($security->getToken()->getUser() === 'anon.' && !$allowAnonymous)
            || !$security->isGranted('user_management', $workspace)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Most dql request required by this controller are paginated.
     * This function transform the results of the repository in an array.
     *
     * @param Paginator $paginator the return value of the Repository using a paginator.
     *
     * @return array.
     */
    private function paginatorToArray($paginator)
    {
        return $this->get('claroline.utilities.paginator_parser')
            ->paginatorToArray($paginator);
    }
}

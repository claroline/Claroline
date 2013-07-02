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
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleSubscribeEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceRoleUnsubscribeEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class UserController extends Controller
{
    const ABSTRACT_WS_CLASS = 'ClarolineCoreBundle:Workspace\AbstractWorkspace';
    const NUMBER_USER_PER_ITERATION = 25;

    /**
     * @Route(
     *     "/{workspaceId}/users/registered/page/{page}",
     *     name="claro_workspace_registered_user_list",
     *     defaults={"page"=1, "search"=""},
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     * @Route(
     *     "/{workspaceId}/users/registered/page/{page}/search/{search}",
     *     name="claro_workspace_registered_user_list_search",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     * @Template("ClarolineCoreBundle:Tool\workspace\user_management:registeredUsers.html.twig")
     */
    public function registeredUsersListAction($workspaceId, $page, $search)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $this->checkRegistration($workspace);
        $repo = $em->getRepository('ClarolineCoreBundle:User');
        $query = ($search == "") ?
            $repo->findByWorkspace($workspace, true):
            $repo->findByWorkspaceAndName($workspace, $search, true);
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
     * @Route(
     *     "/{workspaceId}/users/unregistered/page/{page}",
     *     name="claro_workspace_unregistered_user_list",
     *     defaults={"page"=1, "search"=""},
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     * @Route(
     *     "/{workspaceId}/users/unregistered/page/{page}/search/{search}",
     *     name="claro_workspace_unregistered_user_list_search",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     *
     * @Method("GET")
     *
     * @Template("ClarolineCoreBundle:Tool\workspace\user_management:unregisteredUsers.html.twig")
     */
    public function unregiseredUsersListAction($workspaceId, $page, $search)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace, false);
        $repo = $em->getRepository('ClarolineCoreBundle:User');
        $query = ($search == "") ?
            $repo->findWorkspaceOutsiders($workspace, true):
            $repo->findWorkspaceOutsidersByName($workspace, $search, true);
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
     * @Route(
     *     "/{workspaceId}/user/{userId}",
     *     name="claro_workspace_tools_show_user_parameters",
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$", "userId"="^(?=.*[1-9].*$)\d*$" },
     *     options={"expose"=true}
     * )
     *
     * @Route(
     *     "/{workspaceId}/user/{userId}",
     *     name="claro_workspace_tools_edit_user_parameters",
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$", "userId"="^(?=.*[1-9].*$)\d*$" },
     *     options={"expose"=true}
     * )
     * @Method({"POST", "GET"})
     *
     * @Template("ClarolineCoreBundle:Tool\workspace\user_management:userParameters.html.twig")
     *
     * Renders the user parameter page with its layout and
     * edit the user parameters for the selected workspace.
     *
     * @param integer $workspaceId the workspace id
     * @param integer $userId     the user id
     *
     * @return Response
     */
    public function userParametersAction($workspaceId, $userId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $roleManager = $this->get('claroline.manager.role_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace, false);
        $user = $em->getRepository('ClarolineCoreBundle:User')
            ->find($userId);
        $roleRepo = $em->getRepository('ClarolineCoreBundle:Role');
        $role = $roleRepo->findWorkspaceRoleForUser($user, $workspace);
        $defaultData = array('role' => $role);
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

            if ($newRole->getId() != $roleRepo->findManagerRole($workspace)->getId()) {
                $this->checkRemoveManagerRoleIsValid(array ($userId), $workspace);
            }

            $roleManager->dissociateRole($user, $role);
            $roleManager->associateRole($user, $newRole);
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
     * @Route(
     *     "/{workspaceId}/add/user",
     *     name="claro_workspace_multiadd_user",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @Method("PUT")
     *
     * Adds many users to a workspace.
     * It uses a query string of userIds as parameter (userIds[]=1&userIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function addUsersAction($workspaceId)
    {
        $params = $this->get('request')->query->all();
        $users = array();
        $em = $this->get('doctrine.orm.entity_manager');
        $roleManager = $this->get('claroline.manager.role_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
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
                    $roleManager->associateRole($user, $role);
                }
            }
            $em->flush();
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
     * @Route(
     *     "/{workspaceId}/users",
     *     name="claro_workspace_delete_users",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @Method("DELETE")
     *
     * Removes many users from a workspace.
     * It uses a query string of groupIds as parameter (userIds[]=1&userIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function removeUsersAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $roleManager = $this->get('claroline.manager.role_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
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
    private function checkRemoveManagerRoleIsValid($userIds, $workspace)
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

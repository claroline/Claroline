<?php

namespace Claroline\CoreBundle\Controller;

use Doctrine\ORM\EntityRepository;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class WorkspaceUserController extends Controller
{
    /*******************/
    /* USER MANAGEMENT */
    /*******************/
    const ABSTRACT_WS_CLASS = 'Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace';
    const NUMBER_USER_PER_ITERATION = 25;

    /**
     * Renders the users management page with its layout.
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function usersManagementAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);

        return $this->render(
            'ClarolineCoreBundle:Workspace:tools\user_management.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * Renders the unregistered user list layout for a workspace.
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function unregiseredUsersListAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);

        return $this->render(
            'ClarolineCoreBundle:Workspace:tools\unregistered_user_list_layout.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
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
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkIfAdmin($workspace);
        $user = $em->getRepository('ClarolineCoreBundle:User')
            ->find($userId);
        $roleRepo = $em->getRepository('ClarolineCoreBundle:Role');
        $role = $roleRepo->getEntityRoleForWorkspace($user, $workspace);
        $defaultData = array('role' => $role);
        $form = $this->createFormBuilder($defaultData, array('translation_domain' => 'platform'))
            ->add(
                'role', 'entity', array(
                'class' => 'Claroline\CoreBundle\Entity\Role',
                'property' => 'translationKey',
                'query_builder' => function (EntityRepository $er) use ($workspaceId) {
                    return $er->createQueryBuilder('wr')
                        ->select('role')
                        ->from('Claroline\CoreBundle\Entity\Role', 'role')
                        ->leftJoin('role.workspaceRights', 'rights')
                        ->leftJoin('rights.workspace', 'workspace')
                        ->where('workspace.id = :workspaceId')
                        ->andWhere("role.name != 'ROLE_ANONYMOUS'")
                        ->setParameter('workspaceId', $workspaceId);
                }
            ))
            ->getForm();

        if ($this->getRequest()->getMethod() === 'POST') {
            $request = $this->getRequest();
            $parameters = $request->request->all();
            //cannot bind request: why ?
            $newRole = $roleRepo->find($parameters['form']['role']);

            if ($newRole->getId() != $roleRepo->getManagerRole($workspace)->getId()) {
                $userIds = array($userId);
                $parameters['userIds'] = $userIds;
                $this->checkRemoveManagerRoleIsValid($parameters, $workspace);
            }

            $user->removeRole($role);
            $user->addRole($newRole);
            $em->persist($user);
            $em->flush();
            $route = $this->get('router')->generate(
                'claro_workspace_tools_users_management',
                array('workspaceId' => $workspaceId)
            );

            return new RedirectResponse($route);
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:tools\user_parameters.html.twig',
            array(
                'workspace' => $workspace,
                'user' => $user,
                'form' => $form->createView()
            )
        );
    }

    /**
     * Returns a partial json representation of the registered users of a workspace.
     * It'll search every users whose name match $search.
     *
     * @param string  $search      the search string
     * @param integer $workspaceId the workspace id
     * @param integer $offset      the offset
     *
     * @return Response
     */
    public function searchRegisteredUsersAction($search, $workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        // TODO: quick fix (force doctrine to reload only the concerned roles
        // -- otherwise all the roles loaded by the security context are returned)
        $em->detach($this->get('security.context')->getToken()->getUser());
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->searchRegisteredUsersOfWorkspace(
                $workspaceId,
                $search,
                $offset,
                self::NUMBER_USER_PER_ITERATION
            );
        $users = $this->paginatorToArray($paginatorUsers);
        $content = $this->renderView(
            'ClarolineCoreBundle:Administration:user_list.json.twig',
            array('users' => $users)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a partial json representation of the unregistered users of a workspace.
     * It'll search every users whose name match $search.
     *
     * @param string  $search      the search string
     * @param integer $workspaceId the workspace id
     * @param integer $offset      the offset
     *
     * @return Response
     */
    public function searchUnregisteredUsersAction($search, $workspaceId, $offset)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        // TODO: quick fix (force doctrine to reload only the concerned roles -
        // - otherwise all the roles loaded by the security context are returned)
        $em->detach($this->get('security.context')->getToken()->getUser());
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->searchUnregisteredUsersOfWorkspace(
                $search,
                $workspace,
                $offset,
                self::NUMBER_USER_PER_ITERATION
            );
        $users = $this->paginatorToArray($paginatorUsers);
        $content = $this->renderView(
            'ClarolineCoreBundle:Administration:user_list.json.twig',
            array('users' => $users)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Adds many users to a workspace.
     * It uses a query string of userIds as parameter (userIds[]=1&userIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    //TODO: detach($user)
    //TODO: flush outsite the loop
    //TODO: check is the user isn't already registered
    //TODO: change the userIds into ids
    public function addUsersAction($workspaceId)
    {
        $params = $this->get('request')->query->all();
        $users = array();

        if (isset($params['userIds'])) {
            $em = $this->get('doctrine.orm.entity_manager');
            $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
                ->find($workspaceId);

            foreach ($params['userIds'] as $userId) {
                $user = $em->find('Claroline\CoreBundle\Entity\User', $userId);
                $users[] = $user;
                $user->addRole($em->getRepository('ClarolineCoreBundle:Role')
                    ->getCollaboratorRole($workspace));
                $em->flush();
            }
        }

        $content = $this->renderView(
            'ClarolineCoreBundle:Administration:user_list.json.twig',
            array('users' => $users)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a partial json representation of the registered users of a workspace.
     *
     * @param integer $workspaceId the workspace id
     * @param integer $offset      the offset
     *
     * @return Response
     */
    public function registeredUsersAction($workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        // TODO: quick fix (force doctrine to reload only the concerned roles
        // -- otherwise all the roles loaded by the security context are returned)
        $em->detach($this->get('security.context')->getToken()->getUser());
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->registeredUsersOfWorkspace(
                $workspaceId,
                $offset,
                self::NUMBER_USER_PER_ITERATION
            );
        $users = $this->paginatorToArray($paginatorUsers);
        $content = $this->renderView(
            'ClarolineCoreBundle:Administration:user_list.json.twig',
            array('users' => $users)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Returns a partial json representation of the unregistered users of a workspace.
     *
     * @param integer $workspaceId the workspace id
     * @param integer $offset      the offset
     *
     * @return Response
     */
    public function unregisteredUsersAction($workspaceId, $offset)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkRegistration($workspace);
        // TODO: quick fix (force doctrine to reload only the concerned roles
        // -- otherwise all the roles loaded by the security context are returned)
        $em->detach($this->get('security.context')->getToken()->getUser());
        $paginatorUsers = $em->getRepository('ClarolineCoreBundle:User')
            ->unregisteredUsersOfWorkspace(
                $workspace,
                $offset,
                self::NUMBER_USER_PER_ITERATION
            );
        $users = $this->paginatorToArray($paginatorUsers);
        $content = $this->renderView(
            'ClarolineCoreBundle:Administration:user_list.json.twig',
            array('users' => $users)
        );
        $response = new Response($content);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Removes many users from a workspace.
     * It uses a query string of groupIds as parameter (userIds[]=1&userIds[]=2)
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    //TODO: change userIds into ids
    public function removeUsersAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->find($workspaceId);
        $this->checkIfAdmin($workspace);
        $roles = $em->getRepository('ClarolineCoreBundle:Role')
            ->getWorkspaceRoles($workspace);
        $params = $this->get('request')->query->all();
        $this->checkRemoveManagerRoleIsValid($params, $workspace);

        if (isset($params['userIds'])) {
            foreach ($params['userIds'] as $userId) {
                $user = $em->find('Claroline\CoreBundle\Entity\User', $userId);
                if (null != $user) {
                    foreach ($roles as $role) {
                        $user->removeRole($role);
                    }
                }
            }
        }

        $em->flush();

        return new Response("success", 204);
    }

    /**
     * Checks if the role manager of the user can be changed.
     * There should be awlays at least one manager of a workspace.
     *
     * @param array $parameters "$this->get('request')->query->all();" because I was lazy.
     * In other word an array wich must contains ['userIds'] wich is an array of group ids.
     * @param AbstractWorkspace $workspace the relevant workspace
     *
     * @throws LogicException
     */
    //TODO: change the $parameters parameter.
    private function checkRemoveManagerRoleIsValid($parameters, $workspace)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $countRemovedManagers = 0;
        $managerRole = $em->getRepository('ClarolineCoreBundle:Role')
            ->getManagerRole($workspace);

        if (isset($parameters['userIds'])) {
            foreach ($parameters['userIds'] as $userId) {
                $user = $em->find('Claroline\CoreBundle\Entity\User', $userId);

                if (null !== $user) {
                    if ($workspace == $user->getPersonalWorkspace()) {
                        throw new LogicException(
                            'You can\'t remove the original manager from a personal workspace'
                        );
                    }

                    if ($user->hasRole($managerRole->getName())) {
                        $countRemovedManagers++;
                    }
                }
            }
        }

        $userManagers = $em->getRepository('Claroline\CoreBundle\Entity\User')
            ->getUsersOfWorkspace($workspace, $managerRole, true);
        $countUserManagers = count($userManagers);

        if ($countRemovedManagers >= $countUserManagers) {
            throw new LogicException(
                "You can't remove every managers(you're trying to remove {$countRemovedManagers} "
                . "manager(s) out of {$countUserManagers})"
            );
        }
    }

    /**
     * Checks if the current user can see a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @throws AccessDeniedHttpException
     */
    private function checkRegistration($workspace)
    {
        if (!$this->get('security.context')->isGranted('VIEW', $workspace)) {
            throw new AccessDeniedHttpException();
        }
    }

    /**
     * Checks if the current user is the admin of a workspace.
     *
     * @param AbstractWorkspace $workspace
     *
     * @throws AccessDeniedHttpException
     */
    private function checkIfAdmin($workspace)
    {
        $managerRoleName = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Role')
            ->getManagerRole($workspace)
            ->getName();

        if (!$this->get('security.context')->isGranted($managerRoleName)) {
            throw new AccessDeniedHttpException();
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
        $items = array();

        foreach ($paginator as $item) {
            $items[] = $item;
        }

        return $items;
    }
}
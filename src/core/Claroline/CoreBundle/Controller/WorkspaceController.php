<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Workspace\Configuration;

/**
 * This controller is able to:
 * - list/create/delete/show workspaces.
 * - return some users/groups list (ie: (un)registered users to a workspace).
 * - add/delete users/groups to a workspace.
 */
class WorkspaceController extends Controller
{

    const ABSTRACT_WS_CLASS = 'Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace';
    const NUMBER_USER_PER_ITERATION = 25;
    const NUMBER_GROUP_PER_ITERATION = 10;

    /**
     * Renders the workspace list page with its claroline layout.
     *
     * @throws AccessDeniedHttpException
     * 
     * @return Response
     */
    public function listAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $workspaces = $em->getRepository(self::ABSTRACT_WS_CLASS)->getNonPersonnalWS();

        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_list.html.twig', array('workspaces' => $workspaces)
        );
    }

    /**
     * Renders the registered workspace list for a user.
     *
     * @param integer $userId
     * @param string  $format the format
     *     - 'page': renders the html page with its claroline layout.
     *     - 'json': renders a json response.
     *
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function listWorkspacesByUserAction($userId, $format)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException();
        }

        $em = $this->get('doctrine.orm.entity_manager');

        if ('null' != $userId) {
            $user = $em->find('Claroline\CoreBundle\Entity\User', $userId);
        } else {
            $user = $this->get('security.context')->getToken()->getUser();
        }

        $workspaces = $em->getRepository(self::ABSTRACT_WS_CLASS)->getWorkspacesOfUser($user);

        if ('page' == $format) {
            return $this->render(
                'ClarolineCoreBundle:Workspace:workspace_list.html.twig', array('workspaces' => $workspaces)
            );
        }

        return $this->render("ClarolineCoreBundle:Workspace:workspace_list.{$format}.twig", array('workspaces' => $workspaces));
    }

    /**
     * Renders the workspace creation form
     *
     * @return Response
     */
    public function creationFormAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_WS_CREATOR')) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->get('form.factory')->create(new WorkspaceType());

        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_form.html.twig', array('form' => $form->createView())
        );
    }

    /**
     * Create a workspace from a form sent by POST
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedHttpException
     */
    public function createAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_WS_CREATOR')) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->get('form.factory')->create(new WorkspaceType());
        $form->bindRequest($this->getRequest());

        if ($form->isValid()) {
            $type = $form->get('type')->getData() == 'simple' ?
                    Configuration::TYPE_SIMPLE :
                    Configuration::TYPE_AGGREGATOR;
            $config = new Configuration();
            $config->setWorkspaceType($type);
            $config->setWorkspaceName($form->get('name')->getData());
            $user = $this->get('security.context')->getToken()->getUser();
            $wsCreator = $this->get('claroline.workspace.creator');
            $wsCreator->createWorkspace($config, $user);
            $this->get('session')->setFlash('notice', 'Workspace created');
            $route = $this->get('router')->generate('claro_desktop_index');

            return new RedirectResponse($route);
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_form.html.twig', array('form' => $form->createView())
        );
    }

    /**
     * Delete a workspace and redirects to the desktop_index
     *
     * @param integer $workspaceId
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedHttpException
     */
    public function deleteAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);

        if (false === $this->get('security.context')->isGranted("ROLE_WS_MANAGER_{$workspaceId}", $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $em->remove($workspace);
        $em->flush();

        $this->get('session')->setFlash('notice', 'Workspace deleted');
        $route = $this->get('router')->generate('claro_desktop_index');

        return new RedirectResponse($route);
    }

    /**
     * Renders the workspace_show page with its claroline layout.
     *
     * @param integer $workspaceId
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function showAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $authorization = false;
        $resourcesType = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findAll();

        foreach ($workspace->getWorkspaceRoles() as $role) {
            $this->get('security.context')->isGranted($role->getName()); {
                $authorization = true;
            }
        }

        if ($authorization === false) {
            throw new AccessDeniedHttpException();
        }

        $resourcesInstance = $em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->getWSListableRootResource($workspace);

        return $this->render('ClarolineCoreBundle:Workspace:workspace_show.html.twig', array('workspace' => $workspace, 'resourcesType' => $resourcesType, 'resources' => $resourcesInstance));
    }

    /**
     * Renders the registered user list for a workspace with its claroline layout
     *
     * @param integer $workspaceId
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function listUserByWsAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);

        if (false === $this->get('security.context')->isGranted("ROLE_WS_MANAGER_{$workspaceId}", $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $groups = $em->getRepository('ClarolineCoreBundle:Group')->getGroupsOfWorkspace($workspace);
        $users = $em->getRepository('ClarolineCoreBundle:User')->getUsersOfWorkspace($workspace);

        return $this->render('ClarolineCoreBundle:Workspace:workspace_user_list.html.twig', array('workspace' => $workspace, 'users' => $users, 'groups' => $groups));
    }

    /**
     * Removes a user from a workspace.
     * if user id is null, the user will be the current logged user.
     * if it was requested through ajax, it will response "success".
     * otherwise it'll redirect to the workspace list for a user.
     *
     * @param integer $userId
     *
     * @param integer $workspaceId
     *
     * @return Response|RedirectResponse
     */
    public function removeUserAction($userId, $workspaceId)
    {
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');

        if ('null' != $userId) {
            $user = $em->find('Claroline\CoreBundle\Entity\User', $userId);
        } else {
            $user = $this->get('security.context')->getToken()->getUser();
        }

        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $user = $em->getRepository('ClarolineCoreBundle:User')->find($userId);
        $roles = $workspace->getWorkspaceRoles();

        foreach ($roles as $role) {
            $user->removeRole($role);
        }

        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response("success");
        }

        $route = $this->get('router')->generate('claro_ws_users_ws', array('workspaceId' => $workspaceId));

        return new RedirectResponse($route);
    }

    /**
     * Removes a group from a workspace.
     * if it was requested through ajax, it'll responde "success"
     * otherwise it'll redirect to the user workspace list
     *
     * @param integer $groupId
     * @param integer $workspaceId
     *
     * @return Response|RedirectResponse
     */
    public function removeGroupAction($groupId, $workspaceId)
    {
        $request = $this->get('request');

        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($groupId);
        $roles = $workspace->getWorkspaceRoles();

        foreach ($roles as $role) {
            $group->removeRole($role);
        }

        $em->flush();
        $route = $this->get('router')->generate('claro_ws_users_ws', array('workspaceId' => $workspaceId));

        if ($request->isXmlHttpRequest()) {
            return new Response("success");
        }

        return new RedirectResponse($route);
    }

    /**
     * Renders a list of unregistered users for a workspace.
     * if nbIteration = 1, it'll render users 1-25
     * if nbIteration = 2, it'll render users 26-50
     * if nbIteration = 3, it'll render users 51-75
     * ...
     *
     * @param integer $workspaceId
     * @param integer $nbIteration
     * @param string $format
     *
     * @return Response
     */
    public function limitedUserListAction($workspaceId, $nbIteration, $format)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $users = $em->getRepository('ClarolineCoreBundle:User')->getLazyUnregisteredUsersOfWorkspace($workspace, $nbIteration, self::NUMBER_USER_PER_ITERATION);

        return $this->render("ClarolineCoreBundle:Workspace:dialog_user_list.{$format}.twig", array('users' => $users));
    }

    /**
     * Renders a list of unregistered groups for a workspace
     * if nbIteration = 1, it'll render groups 1-10
     * if nbIteration = 2, it'll render groups 11-20
     * if nbIteration = 3, it'll render groups 21-30
     * ...
     *
     * @param integer $workspaceId
     * @param integer $nbIteration
     * @param string $format
     * @return Response
     */
    public function limitedGroupListAction($workspaceId, $nbIteration, $format)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $groups = $em->getRepository('ClarolineCoreBundle:Group')->getLazyUnregisteredGroupsOfWorkspace($workspace, $nbIteration, self::NUMBER_GROUP_PER_ITERATION);

        return $this->render("ClarolineCoreBundle:Workspace:dialog_group_list.{$format}.twig", array('groups' => $groups));
    }

    /**
     * Renders a list of unregistered users for a workspace.
     * It'll search every users whose username or lastname or firstname match $search.
     *
     * @param string $search
     * @param integer $workspaceId
     * @param string $format
     *
     * @return Response
     */
    public function searchUnregisteredUsersByNamesAction($search, $workspaceId, $format)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $users = $em->getRepository('ClarolineCoreBundle:User')->getUnregisteredUsersOfWorkspaceFromGenericSearch($search, $workspace);

        return $this->render("ClarolineCoreBundle:Workspace:dialog_user_list.{$format}.twig", array('users' => $users));
    }

    /**
     * Adds a user to a workspace
     * if $userId = 'null', the user will be the current logged user
     * if requested through ajax, it'll responde with a json object containing the user datas
     * otherwise it'll redirect to the workspace list.
     *
     * @param integer $userId
     * @param integer $workspaceId
     *
     * @return RedirectResponse
     */
    public function addUserAction($userId, $workspaceId)
    {
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');

        if ('null' != $userId) {
            $user = $em->find('Claroline\CoreBundle\Entity\User', $userId);
        } else {
            $user = $this->get('security.context')->getToken()->getUser();
        }

        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $user->addRole($workspace->getCollaboratorRole());
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->render('ClarolineCoreBundle:Workspace:dialog_user_list.json.twig', array('users' => array($user), 'workspace' => $workspace));
        }

        $route = $this->get('router')->generate('claro_ws_list');

        return new RedirectResponse($route);
    }

    /**
     * Adds a group to a workspace
     * if requested through ajax, it'll responde with a json object containing the group datas
     * otherwise it'll redirect to the workspace list.
     *
     * @param integer $groupId
     * @param integer $workspaceId
     *
     * @return RedirectResponse
     */
    public function addGroupAction($groupId, $workspaceId)
    {
        $request = $this->get('request');
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($groupId);
        $group->addRole($workspace->getCollaboratorRole());
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->render('ClarolineCoreBundle:Workspace:dialog_group_list.json.twig', array('groups' => array($group), 'workspace' => $workspace));
        }

        $route = $this->get('router')->generate('claro_ws_list');

        return new RedirectResponse($route);
    }

    /**
     * Renders a list of unregistered groups for a workspace.
     * It'll search every groups whose name match $search.
     *
     * @param string $search
     * @param integer $workspaceId
     * @param string $format
     *
     * @return Response
     */
    public function searchUnregisteredGroupsByNameAction($search, $workspaceId, $format)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $groups = $em->getRepository('ClarolineCoreBundle:Group')->getUnregisteredGroupsOfWorkspaceFromGenericSearch($search, $workspace);

        return $this->container->get('templating')->renderResponse("ClarolineCoreBundle:Workspace:dialog_group_list.{$format}.twig", array('groups' => $groups));
    }
}

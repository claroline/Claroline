<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Security\SymfonySecurity;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Library\Plugin\Event\DisplayWidgetEvent;

/**
 * This controller is able to:
 * - list/create/delete/show workspaces.
 * - return some users/groups list (ie: (un)registered users to a workspace).
 * - add/delete users/groups to a workspace.
 */
class WorkspaceController extends Controller
{
    const ABSTRACT_WS_CLASS = 'Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace';

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
            'ClarolineCoreBundle:Workspace:list.html.twig',
            array('workspaces' => $workspaces)
        );
    }

    /**
     * Renders the registered workspace list for a user.
     *
     * @param integer $userId
     * @param string $format the format
     *
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function listWorkspacesByUserAction($userId)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->find('Claroline\CoreBundle\Entity\User', $userId);
        $workspaces = $em->getRepository(self::ABSTRACT_WS_CLASS)->getWorkspacesOfUser($user);

        return $this->render(
            "ClarolineCoreBundle:Workspace:list.html.twig",
            array('workspaces' => $workspaces)
        );
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
            'ClarolineCoreBundle:Workspace:form.html.twig',
            array('form' => $form->createView())
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
            $config->setWorkspaceCode($form->get('code')->getData());
            $user = $this->get('security.context')->getToken()->getUser();
            $wsCreator = $this->get('claroline.workspace.creator');
            $wsCreator->createWorkspace($config, $user);
            $this->get('session')->getFlashBag()->add('success', 'Workspace created');
            $route = $this->get('router')->generate('claro_workspace_list');

            return new RedirectResponse($route);
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:form.html.twig',
            array('form' => $form->createView())
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

        return new Response('success', 204);
    }

    /**
     * Renders the home page with its layout.
     *
     * @param integer $workspaceId
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function homeAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);

        return $this->render(
            'ClarolineCoreBundle:Workspace:home.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * Renders the resources page with its layout.
     *
     * @param integer $workspaceId
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function resourcesAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->checkRegistration($workspace);
        $directoryId = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->getRootForWorkspace($workspace)
            ->getId();
        $resourceTypes = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findBy(array('isListable' => true));

        return $this->render(
            'ClarolineCoreBundle:Workspace:resources.html.twig',
            array(
                'workspace' => $workspace,
                'directoryId' => $directoryId,
                'resourceTypes' => $resourceTypes
            )
        );
    }

    /**
     * Returns the id of the current user workspace.
     *
     * @return Response
     */
    public function userWorkspaceIdAction()
    {
        $id = $this->get('security.context')->getToken()->getUser()->getPersonalWorkspace()->getId();

        return new Response($id);
    }

    /**
     *
     * @param type $workspaceId
     * @param type $format
     */
    public function rolesAction($workspaceId, $format)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $wsRoles = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId)->getWorkspaceRoles();

        return $this->render("ClarolineCoreBundle:Workspace:workspace_roles.{$format}.twig", array('roles' => $wsRoles));
    }

    /**
     * Renders the workspace properties page
     */
    public function propertiesAction($workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);

        return $this->render("ClarolineCoreBundle:Workspace:workspace_roles_properties.html.twig", array('workspace' => $workspace, 'masks' => SymfonySecurity::getResourcesMasks()));
    }

    /**
     * Display registered widgets
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function widgetsAction($workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $widgets = $em->getRepository('Claroline\CoreBundle\Entity\Widget')->findAll();

        foreach ($widgets as $widget){
            $eventName = strtolower("widget_{$widget->getName()}_workspace");
            $event = new DisplayWidgetEvent($workspace);
            $this->get('event_dispatcher')->dispatch($eventName, $event);
            $responsesString[$widget->getName()] = $event->getContent();
        }

        return $this->render('ClarolineCoreBundle:Dashboard:widgets\plugins.html.twig', array('widgets' => $responsesString));
    }

    /*******************/
    /* PRIVATE METHODS */
    /*******************/

    private function checkRegistration($workspace)
    {
        $authorization = false;

        foreach ($workspace->getWorkspaceRoles() as $role) {
            if ($this->get('security.context')->isGranted($role->getName())) {
                $authorization = true;
            }
        }

        if ($authorization === false) {
            throw new AccessDeniedHttpException();
        }
    }
}
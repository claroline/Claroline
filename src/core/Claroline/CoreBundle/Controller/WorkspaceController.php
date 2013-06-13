<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceDeleteEvent;

/**
 * This controller is able to:
 * - list/create/delete/show workspaces.
 * - return some users/groups list (ie: (un)registered users to a workspace).
 * - add/delete users/groups to a workspace.
 */
class WorkspaceController extends Controller
{
    const ABSTRACT_WS_CLASS = 'ClarolineCoreBundle:Workspace\AbstractWorkspace';

    /**
     * @Route(
     *     "/",
     *     name="claro_workspace_list",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Renders the workspace list page with its claroline layout.
     *
     * @return Response
     */
    public function listAction()
    {
        $datas = $this->get('claroline.workspace.organizer')->getDatasForWorkspaceList(true);

        return $this->render(
            'ClarolineCoreBundle:Workspace:list.html.twig',
            array(
                'workspaces' => $datas['workspaces'],
                'tags' => $datas['tags'],
                'tagWorkspaces' => $datas['tagWorkspaces'],
                'hierarchy' => $datas['hierarchy'],
                'rootTags' => $datas['rootTags'],
                'displayable' => $datas['displayable']
            )
        );
    }

    /**
     * @Route(
     *     "/user",
     *     name="claro_workspace_by_user",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Renders the registered workspace list for a user.
     *
     * @return Response
     */
    public function listWorkspacesByUserAction()
    {
        $this->assertIsGranted('ROLE_USER');
        $em = $this->get('doctrine.orm.entity_manager');
        $token = $this->get('security.context')->getToken();
        $user = $token->getUser();
        $roles = $this->get('claroline.security.utilities')->getRoles($token);

        $workspaces = $em->getRepository(self::ABSTRACT_WS_CLASS)
            ->findByRoles($roles);
        $tags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findNonEmptyTagsByUser($user);
        $relTagWorkspace = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findByUser($user);

        $tagWorkspaces = array();

        foreach ($relTagWorkspace as $tagWs) {

            if (empty($tagWorkspaces[$tagWs['tag_id']])) {
                $tagWorkspaces[$tagWs['tag_id']] = array();
            }
            $tagWorkspaces[$tagWs['tag_id']][] = $tagWs['rel_ws_tag'];
        }
        $tagsHierarchy = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTagHierarchy')
            ->findAllByUser($user);
        $rootTags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findRootTags($user);
        $hierarchy = array();

        // create an array : tagId => [direct_children_id]
        foreach ($tagsHierarchy as $tagHierarchy) {

            if ($tagHierarchy->getLevel() === 1) {

                if (!isset($hierarchy[$tagHierarchy->getParent()->getId()]) ||
                    !is_array($hierarchy[$tagHierarchy->getParent()->getId()])) {

                    $hierarchy[$tagHierarchy->getParent()->getId()] = array();
                }
                $hierarchy[$tagHierarchy->getParent()->getId()][] = $tagHierarchy->getTag();
            }
        }

        // create an array indicating which tag is displayable
        // a tag is displayable if it or one of his children contains is associated to a workspace
        $displayable = array();
        $allTags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findByUser($user);

        foreach ($allTags as $oneTag) {
            $oneTagId = $oneTag->getId();
            $displayable[$oneTagId] = $this->isTagDisplayable($oneTagId, $tagWorkspaces, $hierarchy);
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:list_my_workspaces.html.twig',
            array(
                'user' => $user,
                'workspaces' => $workspaces,
                'tags' => $tags,
                'tagWorkspaces' => $tagWorkspaces,
                'hierarchy' => $hierarchy,
                'rootTags' => $rootTags,
                'displayable' => $displayable
            )
        );
    }

    /**
     * @Route(
     *     "/new/form",
     *     name="claro_workspace_creation_form"
     * )
     * @Method("GET")
     *
     * Renders the workspace creation form.
     *
     * @return Response
     */
    public function creationFormAction()
    {
        $this->assertIsGranted('ROLE_WS_CREATOR');
        $form = $this->get('form.factory')
            ->create(new WorkspaceType());

        return $this->render(
            'ClarolineCoreBundle:Workspace:form.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Creates a workspace from a form sent by POST.
     *
     * @Route(
     *     "/",
     *     name="claro_workspace_create"
     * )
     * @Method("POST")
     *
     * @return RedirectResponse
     */
    public function createAction()
    {
        $this->assertIsGranted('ROLE_WS_CREATOR');
        $form = $this->get('form.factory')
            ->create(new WorkspaceType());
        $form->bind($this->getRequest());

        $templateDir = $this->container->getParameter('claroline.param.templates_directory');
        $ds = DIRECTORY_SEPARATOR;

        if ($form->isValid()) {
            $type = $form->get('type')->getData() == 'simple' ?
                Configuration::TYPE_SIMPLE :
                Configuration::TYPE_AGGREGATOR;
            $config = Configuration::fromTemplate($templateDir.$ds.$form->get('template')->getData()->getHash());
            $config->setWorkspaceType($type);
            $config->setWorkspaceName($form->get('name')->getData());
            $config->setWorkspaceCode($form->get('code')->getData());
            $user = $this->get('security.context')->getToken()->getUser();
            $wsCreator = $this->get('claroline.workspace.creator');
            $wsCreator->createWorkspace($config, $user);
            $this->get('claroline.security.token_updater')->update($this->get('security.context')->getToken());
            $route = $this->get('router')->generate('claro_workspace_list');

            return new RedirectResponse($route);
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:form.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}",
     *     name="claro_workspace_delete",
     *     options={"expose"=true},
     *     requirements={"workspaceId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @Method("DELETE")
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function deleteAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $this->assertIsGranted('DELETE', $workspace);
        $log = new LogWorkspaceDeleteEvent($workspace);
        $this->get('event_dispatcher')->dispatch('log', $log);
        $em->remove($workspace);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * Renders the left tool bar. Not routed.
     *
     * @param $_workspace
     *
     * @return Response
     */
    public function renderToolListAction($workspaceId, $_breadcrumbs)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        if ($_breadcrumbs != null) {
            //for manager.js, id = 0 => "no root".
            if ($_breadcrumbs[0] != 0) {
                $rootId = $_breadcrumbs[0];
            } else {
                $rootId = $_breadcrumbs[1];
            }
            $workspace = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->find($rootId)->getWorkspace();
        } else {
            $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
                ->find($workspaceId);
        }

        if (!$this->get('security.context')->isGranted('OPEN', $workspace)) {
            throw new AccessDeniedException();
        }

        $currentRoles = $this->get('claroline.security.utilities')
            ->getRoles($this->get('security.context')->getToken());

        $workspaceOrderTools = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findBy(array('workspace' => $workspace));

        $tools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findByRolesAndWorkspace($currentRoles, $workspace, true);
        $toolsWithTranslation = array();

        foreach ($tools as $tool) {
            $toolWithTranslation['tool'] = $tool;
            $found = false;

            foreach ($workspaceOrderTools as $workspaceOrderedTool) {
                if ($workspaceOrderedTool->getTool() === $tool) {
                    $toolWithTranslation['name'] = $workspaceOrderedTool->getName();
                    $found = true;
                }
            }

            if (!$found) {
                $toolWithTranslation['name'] = $tool->getName();
            }

            $toolsWithTranslation[] = $toolWithTranslation;
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:tool_list.html.twig',
            array('toolsWithTranslation' => $toolsWithTranslation, 'workspace' => $workspace)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/open/tool/{toolName}",
     *     name="claro_workspace_open_tool",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Opens a tool.
     *
     * @param type $toolName
     * @param type $workspaceId
     *
     * @return Response
     */
    public function openToolAction($toolName, $workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);
        $this->assertIsGranted($toolName, $workspace);
        $event = new DisplayToolEvent($workspace);
        $eventName = 'open_tool_workspace_'.$toolName;
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if (is_null($event->getContent())) {
            throw new \Exception(
                "Tool '{$toolName}' didn't return any Response for tool event '{$eventName}'."
            );
        }

        $log = new LogWorkspaceToolReadEvent($workspace, $toolName);
        $this->get('event_dispatcher')->dispatch('log', $log);

        return new Response($event->getContent());
    }

    /**
     * @Route(
     *     "/{workspaceId}/widgets",
     *     name="claro_workspace_widgets"
     * )
     * @Method("GET")
     *
     * Display registered widgets.
     *
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @todo Reduce the number of sql queries for this action (-> dql)
     */
    public function widgetsAction($workspaceId)
    {
        // No right checking is done : security is delegated to each widget renderer
        // Is that a good idea ?
        $responsesString = '';
        $configs = $this->get('claroline.widget.manager')
            ->generateWorkspaceDisplayConfig($workspaceId);
        $em = $this->getDoctrine()->getManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);

        foreach ($configs as $config) {
            if ($config->isVisible()) {
                $eventName = "widget_{$config->getWidget()->getName()}_workspace";
                $event = new DisplayWidgetEvent($workspace);
                $this->get('event_dispatcher')->dispatch($eventName, $event);
                $responsesString[strtolower($config->getWidget()->getName())] = $event->getContent();
            }
        }

        return $this->render(
            'ClarolineCoreBundle:Widget:widgets.html.twig',
            array('widgets' => $responsesString)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/open",
     *     name="claro_workspace_open"
     * )
     * @Method("GET")
     *
     * Open the first tool of a workspace.
     *
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function openAction($workspaceId)
    {
        $em = $this->getDoctrine()->getManager();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);

        if ('anon.' != $this->get('security.context')->getToken()->getUser()) {
            $roles = $em->getRepository('ClarolineCoreBundle:Role')->findByWorkspace($workspace);
            $foundRole = null;

            foreach ($roles as $wsRole) {
                foreach ($this->get('security.context')->getToken()->getUser()->getRoles() as $userRole) {
                    if ($userRole == $wsRole->getName()) {
                        $foundRole = $userRole;
                    }
                }
            }

            $isAdmin = $this->get('security.context')->getToken()->getUser()->hasRole('ROLE_ADMIN');

            if ($foundRole === null && !$isAdmin) {
                throw new AccessDeniedException('No role found in that workspace');
            }

            if ($isAdmin) {
                //admin always open the home.
                $openedTool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
                    ->findBy(array('name' => 'home'));
            } else {
                $openedTool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
                    ->findByRolesAndWorkspace(array($foundRole), $workspace, true);
            }

        } else {
            $foundRole = 'ROLE_ANONYMOUS';
            $openedTool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
                ->findByRolesAndWorkspace(array('ROLE_ANONYMOUS'), $workspace, true);
        }

        if ($openedTool == null) {
            throw new AccessDeniedException("No tool found for role {$foundRole}");
        }

        $route = $this->get('router')->generate(
            'claro_workspace_open_tool',
            array('workspaceId' => $workspaceId, 'toolName' => $openedTool[0]->getName())
        );

        return new RedirectResponse($route);
    }

    private function assertIsGranted($attributes, $object = null)
    {
        if (false === $this->get('security.context')->isGranted($attributes, $object)) {
            throw new AccessDeniedException();
        }
    }
}
<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Library\Event\LogWorkspaceDeleteEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

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
        $workspaces = $em->getRepository(self::ABSTRACT_WS_CLASS)->findNonPersonal();
        $tags = $em->getRepository('ClarolineCoreBundle:Workspace\WorkspaceTag')
            ->findNonEmptyAdminTags();
        $relTagWorkspace = $em->getRepository('ClarolineCoreBundle:Workspace\RelWorkspaceTag')
            ->findByAdmin();

        $tagWorkspaces = array();

        foreach ($relTagWorkspace as $tagWs) {

            if (empty($tagWorkspaces[$tagWs['tag_id']])) {
                $tagWorkspaces[$tagWs['tag_id']] = array();
            }
            $tagWorkspaces[$tagWs['tag_id']][] = $tagWs['rel_ws_tag'];
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:list.html.twig',
            array('workspaces' => $workspaces, 'tags' => $tags, 'tagWorkspaces' => $tagWorkspaces)
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
     * @throws AccessDeniedHttpException
     *
     * @return Response
     */
    public function listWorkspacesByUserAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_USER')) {
            throw new AccessDeniedHttpException();
        }

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

        return $this->render(
            'ClarolineCoreBundle:Workspace:list_my_workspaces.html.twig',
            array(
                'user' => $user,
                'workspaces' => $workspaces,
                'tags' => $tags,
                'tagWorkspaces' => $tagWorkspaces
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
        if (false === $this->get('security.context')->isGranted('ROLE_WS_CREATOR')) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->get('form.factory')
            ->create(new WorkspaceType($this->container->getParameter('claroline.param.templates_directory')));

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
     *
     * @throws AccessDeniedHttpException
     */
    public function createAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_WS_CREATOR')) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->get('form.factory')
            ->create(new WorkspaceType($this->container->getParameter('claroline.param.templates_directory')));
        $form->bind($this->getRequest());

        if ($form->isValid()) {
            $type = $form->get('type')->getData() == 'simple' ?
                Configuration::TYPE_SIMPLE :
                Configuration::TYPE_AGGREGATOR;
            $config = Configuration::fromTemplate($form->get('template')->getData());
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
     * @return RedirectResponse
     *
     * @throws AccessDeniedHttpException
     */
    public function deleteAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);

        if (false === $this->get('security.context')->isGranted("DELETE", $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $em->remove($workspace);
        $em->flush();

        $log = new LogWorkspaceDeleteEvent($workspace);
        $this->get('event_dispatcher')->dispatch('log', $log);

        return new Response('success', 204);
    }

    /**
     * Renders the left tool bar. Not routed.
     *
     * @param type $workspaceId
     *
     * @return Response
     */
    public function renderToolListAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);

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

        if (false === $this->get('security.context')->isGranted($toolName, $workspace)) {
            throw new AccessDeniedHttpException();
        }

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

    //todo dql for this
    /**
     * @Route(
     *     "/{workspaceId}/widgets",
     *     name="claro_workspace_widgets"
     * )
     * @Method("GET")
     *
     * Display registered widgets.
     *
     * @param $workspaceId the workspace id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function widgetsAction($workspaceId)
    {
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
                throw new AccessDeniedHttpException('No role found in that workspace');
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
            throw new AccessDeniedHttpException("No tool found for role {$foundRole}");
        }

        $route = $this->get('router')->generate(
            'claro_workspace_open_tool',
            array('workspaceId' => $workspaceId, 'toolName' => $openedTool[0]->getName())
        );

        return new RedirectResponse($route);
    }
}
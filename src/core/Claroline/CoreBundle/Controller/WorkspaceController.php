<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Claroline\CoreBundle\Entity\Workspace\WorkspaceTag;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Event\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\Event\Log\LogWorkspaceToolReadEvent;
use Claroline\CoreBundle\Event\Event\Log\LogWorkspaceDeleteEvent;

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
     * @Template()
     *
     * Renders the workspace list page with its claroline layout.
     *
     * @return Response
     */
    public function listAction()
    {
        $datas = $this->get('claroline.workspace.organizer')->getDatasForWorkspaceList(false);

        return array(
            'workspaces' => $datas['workspaces'],
            'tags' => $datas['tags'],
            'tagWorkspaces' => $datas['tagWorkspaces'],
            'hierarchy' => $datas['hierarchy'],
            'rootTags' => $datas['rootTags'],
            'displayable' => $datas['displayable']
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
     * @Template()
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

        return array(
            'user' => $user,
            'workspaces' => $workspaces,
            'tags' => $tags,
            'tagWorkspaces' => $tagWorkspaces,
            'hierarchy' => $hierarchy,
            'rootTags' => $rootTags,
            'displayable' => $displayable
        );
    }

    private function isTagDisplayable($tagId, array $tagWorkspaces, array $hierarchy)
    {
        $displayable = false;

        if (isset($tagWorkspaces[$tagId]) && count($tagWorkspaces[$tagId]) > 0) {
            $displayable = true;
        } else {

            if (isset($hierarchy[$tagId]) && count($hierarchy[$tagId]) > 0) {
                $children = $hierarchy[$tagId];

                foreach ($children as $child) {

                    $displayable = $this->isTagDisplayable($child->getId(), $tagWorkspaces, $hierarchy);

                    if ($displayable) {
                        break;
                    }
                }
            }
        }

        return $displayable;
    }

    /**
     * @Route(
     *     "/new/form",
     *     name="claro_workspace_creation_form"
     * )
     * @Method("GET")
     *
     * @Template()
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

        return array('form' => $form->createView());
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
     * @Template("ClarolineCoreBundle:Workspace:creationForm.html.twig")
     * @return RedirectResponse
     */
    public function createAction()
    {
        $this->assertIsGranted('ROLE_WS_CREATOR');
        $form = $this->get('form.factory')
            ->create(new WorkspaceType());
        $form->handleRequest($this->getRequest());

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

        return array('form' => $form->createView());
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

        $log = $this->get('claroline.event.event_dispatcher')->dispatch(
            'log',
            'Log\LogWorkspaceDeletele',
            array($workspace)
        );

        $em->remove($workspace);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @Template()
     *
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

        $orderedTools = $em->getRepository('ClarolineCoreBundle:Tool\OrderedTool')
            ->findByWorkspaceAndRoles($workspace, $currentRoles);

        return array(
            'orderedTools' => $orderedTools,
            'workspace' => $workspace
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
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            'open_tool_workspace_'.$toolName,
            'DisplayTool',
            array($workspace)
        );

        $log = $this->get('claroline.event.event_dispatcher')->dispatch(
            'log',
            'Log\LogWorkspaceToolRead',
            array($workspace,$toolName)
        );

        return new Response($event->getContent());
    }

    /**
     * @Route(
     *     "/{workspaceId}/widgets",
     *     name="claro_workspace_widgets"
     * )
     * @Method("GET")
     *
     * @Template("ClarolineCoreBundle:Widget:widgets.html.twig")
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

        $rightToConfigure = $this->get('security.context')->isGranted('parameters', $workspace);

        $widgets = array();

        foreach ($configs as $config) {
            if ($config->isVisible()) {
                $eventName = "widget_{$config->getWidget()->getName()}_workspace";
                $event = new DisplayWidgetEvent($workspace);
                $this->get('event_dispatcher')->dispatch($eventName, $event);

                if ($event->hasContent()) {
                    $widget['id'] = $config->getWidget()->getId();
                    if ($event->hasTitle()) {
                        $widget['title'] = $event->getTitle();
                    } else {
                        $widget['title'] = strtolower($config->getWidget()->getName());
                    }
                    $widget['content'] = $event->getContent();
                    $widget['configurable'] = (
                        $rightToConfigure
                        and $config->isLocked() !== true
                        and $config->getWidget()->isConfigurable()
                    );

                    $widgets[] = $widget;
                }
            }
        }

        return array(
            'widgets' => $widgets,
            'isDesktop' => false,
            'workspaceId' => $workspaceId
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
                    ->findDisplayedByRolesAndWorkspace(array($foundRole), $workspace);
            }

        } else {
            $foundRole = 'ROLE_ANONYMOUS';
            $openedTool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
                ->findDisplayedByRolesAndWorkspace(array('ROLE_ANONYMOUS'), $workspace);
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

    /**
     * @Route(
     *     "/search/role/code/{code}",
     *     name="claro_resource_find_role_by_code",
     *     options={"expose"=true}
     * )
     */
    public function findRoleByWorkspaceCodeAction($code)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $roles = $em->getRepository('ClarolineCoreBundle:Role')->findByWorkspaceCodeTag($code);
        $arWorkspace = array();

        foreach ($roles as $role) {
            $arWorkspace[$role->getWorkspace()->getCode()][$role->getName()] = array(
                'name' => $role->getName(),
                'translation_key' => $role->getTranslationKey(),
                'id' => $role->getId(),
                'workspace' => $role->getWorkspace()->getName()
            );
        }

        return new JsonResponse($arWorkspace);
    }

    private function assertIsGranted($attributes, $object = null)
    {
        if (false === $this->get('security.context')->isGranted($attributes, $object)) {
            throw new AccessDeniedException();
        }
    }
}
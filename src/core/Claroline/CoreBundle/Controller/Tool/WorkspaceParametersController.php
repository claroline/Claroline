<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Tool\WorkspaceOrderedTool;
use Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole;
use Claroline\CoreBundle\Library\Event\ConfigureWidgetWorkspaceEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Form\WorkspaceEditType;
use Claroline\CoreBundle\Form\WorkspaceOrderToolEditType;
use Claroline\CoreBundle\Form\WorkspaceTemplateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class WorkspaceParametersController extends Controller
{
    /**
     * @Route(
     *     "/{workspaceId}/widget",
     *     name="claro_workspace_widget_properties"
     * )
     * @Method("GET")
     *
     * Renders the workspace widget properties page.
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceWidgetsPropertiesAction($workspaceId)
    {
        $em = $this->getDoctrine()->getManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $configs = $this->get('claroline.widget.manager')
            ->generateWorkspaceDisplayConfig($workspaceId);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:widget_properties.html.twig',
            array('workspace' => $workspace, 'configs' => $configs)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/widget/{widgetId}/baseconfig/{displayConfigId}/invertvisible",
     *     name="claro_workspace_widget_invertvisible",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * Inverts the visibility boolean of a widget in the specified workspace.
     * If the DisplayConfig entity for the workspace doesn't exist in the database
     * yet, it's created here.
     *
     * @param integer $workspaceId
     * @param integer $widgetId
     * @param integer $displayConfigId The displayConfig defined by the administrator: it's the
     *                                 configuration entity for widgets)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceInvertVisibleWidgetAction($workspaceId, $widgetId, $displayConfigId)
    {
        $em = $this->getDoctrine()->getManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->find($widgetId);
        $displayConfig = $em
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('workspace' => $workspace, 'widget' => $widget));

        if ($displayConfig == null) {
            $displayConfig = new DisplayConfig();
            $baseConfig = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
                ->find($displayConfigId);
            $displayConfig->setParent($baseConfig);
            $displayConfig->setWidget($widget);
            $displayConfig->setWorkspace($workspace);
            $displayConfig->setVisible($baseConfig->isVisible());
            $displayConfig->setLock(true);
            $displayConfig->setDesktop(false);
            $displayConfig->invertVisible();
        } else {
            $displayConfig->invertVisible();
        }

        $em->persist($displayConfig);
        $em->flush();

        return new Response('success');
    }

    /**
     * @Route(
     *     "/{workspaceId}/widget/{widgetId}/configuration",
     *     name="claro_workspace_widget_configuration",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * Asks a widget to render its configuration page for a workspace.
     *
     * @param integer $workspaceId
     * @param integer $widgetId
     *
     * @return Response
     */
    public function workspaceConfigureWidgetAction($workspaceId, $widgetId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->find($widgetId);
        $event = new ConfigureWidgetWorkspaceEvent($workspace);
        $eventName = "widget_{$widget->getName()}_configuration_workspace";
        $this->get('event_dispatcher')->dispatch($eventName, $event);
        if ($event->getContent() !== '') {
            return $this->render(
                'ClarolineCoreBundle:Tool\workspace\parameters:widget_configuration.html.twig',
                array('content' => $event->getContent(), 'workspace' => $workspace)
            );
        }

        throw new \Exception("event {$eventName} didn't return any Response");
    }

    /**
     * @Route(
     *     "/{workspaceId}/tools",
     *     name="claro_workspace_tools_roles"
     * )
     * @Method("GET")
     */
    public function workspaceToolsRolesAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $wsRoles = $em->getRepository('ClarolineCoreBundle:Role')->findByWorkspace($workspace);
        $anonRole = $em->getRepository('ClarolineCoreBundle:Role')->findBy(array('name' => 'ROLE_ANONYMOUS'));
        $wsRoles = array_merge($wsRoles, $anonRole);
        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findBy(array('workspace' => $workspaceId), array('order' => 'ASC'));

        /*
         * Creates an easy to use array with tools visibility permissons.
         * The array has the following structure:
         *
         * array[$order] => array(
         *  'tool' => $tool'
         *  'visibility' => array('
         *      ROLE_1 => $bool,
         *      ROLE_2 => $bool,
         *      ROLE_3 => $bool)
         * );
         */

        /*
         * Loading all the datas from the WorkspaceToolRole entities
         * so doctrine won't do a new request every time the isToolVisibleForRoleInWorkspace()
         * is fired.
         */
        $wtr = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole')->findByWorkspace($workspace);

        foreach ($wot as $orderedTool) {

            if ($orderedTool->getTool()->isDisplayableInWorkspace()) {
                //creates the visibility array
                foreach ($wsRoles as $role) {
                    $isVisible = false;
                    //is the tool visible for a role in a workspace ?
                    foreach ($wtr as $workspaceToolRole) {
                        if ($workspaceToolRole->getRole() == $role
                            && $workspaceToolRole->getWorkspaceOrderedTool()->getTool() == $orderedTool->getTool()
                            && $workspaceToolRole->getWorkspaceOrderedTool()->getWorkspace() == $workspace) {
                            $isVisible = true;
                        }
                    }
                    $roleVisibility[$role->getId()] = $isVisible;
                }
                $toolsPermissions[] = array(
                    'tool' => $orderedTool,
                    'visibility' => $roleVisibility
                );
            }
        }

        $undisplayedTools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findByWorkspace($workspace, false);

        //this is the missing part of the array
        $toFill = array();
        //default display_order if there is no WorkspaceOrderTool
        $nextDisplayOrder = 1;

        if (!empty($toolsPermissions)) {
            //the next display_order will be the incrementation of the last WorkspaceOrderTool display_order
            $nextDisplayOrder = $orderedTool->getOrder() + 1;
        }

        foreach ($undisplayedTools as $undisplayedTool) {

            if ($undisplayedTool->isDisplayableInWorkspace()) {
                $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
                    ->findOneBy(array('workspace' => $workspaceId, 'tool' => $undisplayedTool->getId()));

                //create a WorkspaceOrderedTool for each Tool that hasn't already one
                if ($wot === null) {
                    $wot = new WorkspaceOrderedTool();
                    $wot->setOrder($nextDisplayOrder++);
                    $wot->setTool($undisplayedTool);
                    $wot->setWorkspace($workspace);
                    $wot->setName(
                        $this->container->get('translator')->trans(
                            $undisplayedTool->getName(),
                            array(),
                            'tools'
                        )
                    );
                    $em->persist($wot);
                    $em->flush();
                } else {
                    continue;
                }

                foreach ($wsRoles as $role) {
                    $roleVisibility[$role->getId()] = false;
                }
                $toFill[] = array('tool' => $wot, 'visibility' => $roleVisibility);
            }
        }

        $toolsPermissions = $this->container->get('claroline.utilities.misc')->arrayFill($toolsPermissions, $toFill);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:tool_roles.html.twig',
            array(
                'roles' => $wsRoles,
                'workspace' => $workspace,
                'toolPermissions' => $toolsPermissions
            )
        );
    }

    /**
     * @Route(
     *     "/remove/tool/{toolId}/workspace/{workspaceId}/role/{roleId}",
     *     name="claro_tool_workspace_remove",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * Remove a tool from a role in a workspace.
     *
     * @param integer $toolId
     * @param integer $roleId
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function workspaceRemoveToolAction($toolId, $roleId, $workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findOneBy(array('workspace' => $workspaceId, 'tool' => $toolId));

        $wtr = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole')
            ->findOneBy(array('role' => $roleId, 'workspaceOrderedTool' => $wot));
        $em->remove($wtr);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/add/tool/{toolId}/position/{position}/workspace/{workspaceId}/role/{roleId}",
     *     name="claro_tool_workspace_add",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * Adds a tool to a role in a workspace.
     *
     * @param integer $toolId
     * @param integer $roleId
     * @param integer $workspaceId
     * @param integer $position
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function workspaceAddToolAction($toolId, $roleId, $workspaceId, $position)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $role = $em->getRepository('ClarolineCoreBundle:Role')->find($roleId);
        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findOneBy(array('workspace' => $workspaceId, 'tool' => $toolId));
        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);

        if ($wot === null) {
            $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);
            $wot = new WorkspaceOrderedTool();
            $wot->setOrder($position);
            $wot->setTool($tool);
            $wot->setWorkspace($workspace);
            $wot->setName($tool->getName());
            $em->persist($wot);
        }

        $wtr = new WorkspaceToolRole();
        $wtr->setRole($role);
        $wtr->setWorkspaceOrderedTool($wot);
        $em->persist($wtr);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/move/tool/{toolId}/position/{position}/workspace/{workspaceId}",
     *     name="claro_tool_workspace_move",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * This method switch the position of a tool with an other one.
     *
     * @param integer $toolId
     * @param integer $position
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function workspaceMoveToolAction($toolId, $position, $workspaceId)
    {
        if (intval($position) == null) {
            throw new \RuntimeException('The $position value must be an integer');
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);

        $movingTool = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
           ->findOneBy(array('tool' => $tool, 'workspace' => $workspace));

        if ($movingTool === null) {
            throw new \RuntimeException(
                "There is no WorkspaceOrderedTool for {$tool->getName()} in {$workspace->getName()}"
            );
        }

        $switchTool = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
           ->findOneBy(array('order' => $position, 'workspace' => $workspace));

         //if a tool is already at this position, he must go "far away"
        if ($switchTool !== null && $movingTool !== null) {
            //go far away ! Integrety constraints.
            $newPosition = $movingTool->getOrder();
            $switchTool->setOrder('99');
            $em->persist($switchTool);
        }

        $em->flush();

         //the tool must exists
        if ($movingTool !== null) {
            $movingTool->setOrder(intval($position));
            $em->persist($movingTool);
        }

        //put the original tool back.
        if ($switchTool !== null) {
            $switchTool->setOrder($newPosition);
            $em->persist($switchTool);
        }

        $em->flush();

        return new Response('success');
    }

    /**
     * @Route(
     *     "/{workspaceId}/resource/rights/form",
     *     name="claro_workspace_resource_rights_form"
     * )
     * @Method("GET")
     *
     * @param integer $workspaceId
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function workspaceResourceRightsFormAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->findWorkspaceRoot($workspace);
        $roleRights = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findNonAdminRights($resource);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:resources_rights.html.twig',
            array('workspace' => $workspace, 'resource' => $resource, 'roleRights' => $roleRights)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/resource/rights/form/role/{roleId}",
     *     name="claro_workspace_resource_rights_creation_form"
     * )
     * @Method("GET")
     *
     * @param integer $workspaceId
     * @param integer $roleId
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function workspaceResourceRightsCreationFormAction($workspaceId, $roleId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $resource = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->findWorkspaceRoot($workspace);
        $role = $em->getRepository('ClarolineCoreBundle:Role')
            ->find($roleId);
        $config = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findOneBy(array('resource' => $resource, 'role' => $role));
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findBy(array('isVisible' => true));

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:resource_rights_creation.html.twig',
            array(
                'workspace' => $workspace,
                'configs' => array($config),
                'resourceTypes' => $resourceTypes,
                'resourceId' => $resource->getId(),
                'roleId' => $roleId
            )
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/form/export",
     *     name="claro_workspace_export_form"
     * )
     * @Method("GET")
     *
     * @param integer $workspaceId
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function workspaceExportFormAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->get('form.factory')->create(new WorkspaceTemplateType());

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig',
            array(
                'form' => $form->createView(),
                'workspace' => $workspace)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/export",
     *     name="claro_workspace_export"
     * )
     * @Method("POST")
     *
     * @param integer $workspaceId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws AccessDeniedHttpException
     */
    public function workspaceExportAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $request = $this->getRequest();
        $form = $this->createForm(new WorkspaceTemplateType());
        $form->bind($request);

        if ($form->isValid()) {
            $name = $form->get('name')->getData();
            $this->get('claroline.workspace.exporter')->export($workspace, $name);
            $route = $this->get('router')->generate(
                'claro_workspace_open_tool',
                array('toolName' => 'parameters', 'workspaceId' => $workspace->getId())
            );

            return new RedirectResponse($route);
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:template.html.twig',
            array(
                'form' => $form->createView(),
                'workspace' => $workspace)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/editform",
     *     name="claro_workspace_edit_form"
     * )
     * @Method("GET")
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceEditFormAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(new WorkspaceEditType(), $workspace);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:workspace_edit.html.twig',
            array('form' => $form->createView(), 'workspace' => $workspace)
        );
    }


    /**
     * @Route(
     *     "/{workspaceId}/edit",
     *     name="claro_workspace_edit"
     * )
     * @Method("POST")
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceEditAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $wsRegisteredName = $workspace->getName();
        $wsRegisteredCode = $workspace->getCode();
        $form = $this->createForm(new WorkspaceEditType(), $workspace);
        $request = $this->getRequest();
        $form->bind($request);

        if ($form->isValid()) {
            $em->persist($workspace);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_open_tool',
                    array(
                        'workspaceId' => $workspaceId,
                        'toolName' => 'parameters'
                    )
                )
            );
        } else {
            $workspace->setName($wsRegisteredName);
            $workspace->setCode($wsRegisteredCode);
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:workspace_edit.html.twig',
            array('form' => $form->createView(), 'workspace' => $workspace)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/tools/{workspaceOrderToolId}/editform",
     *     name="claro_workspace_order_tool_edit_form"
     * )
     * @Method("GET")
     *
     * @param integer $workspaceId
     * @param integer $workspaceOrderToolId
     *
     * @return Response
     */
    public function workspaceOrderToolEditFormAction($workspaceId, $workspaceOrderToolId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')->find($workspaceOrderToolId);

        $form = $this->createForm(new WorkspaceOrderToolEditType(), $wot);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:workspace_order_tool_edit.html.twig',
            array('form' => $form->createView(), 'workspace' => $workspace, 'wot' => $wot)
        );
    }

    /**
     * @Route(
     *     "/{workspaceId}/tools/{workspaceOrderToolId}/edit",
     *     name="claro_workspace_order_tool_edit"
     * )
     * @Method("POST")
     *
     * @param integer $workspaceId
     * @param integer $workspaceOrderToolId
     *
     * @return Response
     */
    public function workspaceOrderToolEditAction($workspaceId, $workspaceOrderToolId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')->find($workspaceOrderToolId);

        $form = $this->createForm(new WorkspaceOrderToolEditType(), $wot);
        $request = $this->getRequest();
        $form->bind($request);

        if ($form->isValid()) {
            $em->persist($wot);
            $em->flush();

            return $this->redirect(
                $this->generateUrl(
                    'claro_workspace_tools_roles',
                    array('workspaceId' => $workspaceId)
                )
            );
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:workspace_order_tool_edit.html.twig',
            array('form' => $form->createView(), 'workspace' => $workspace, 'wot' => $wot)
        );
    }
}

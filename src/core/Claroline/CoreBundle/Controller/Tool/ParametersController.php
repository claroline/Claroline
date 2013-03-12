<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Tool\DesktopTool;
use Claroline\CoreBundle\Entity\Tool\WorkspaceOrderedTool;
use Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole;
use Claroline\CoreBundle\Library\Event\ConfigureWidgetWorkspaceEvent;
use Claroline\CoreBundle\Library\Event\ConfigureWidgetDesktopEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Form\WorkspaceEditType;
use Claroline\CoreBundle\Form\WorkspaceTemplateType;
use Symfony\Component\Yaml\Yaml;

class ParametersController extends Controller
{
    /**
     * Renders the workspace widget properties page.
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceWidgetsPropertiesAction($workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
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
        $em = $this->getDoctrine()->getEntityManager();
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
        $eventName = strtolower("widget_{$widget->getName()}_configuration_workspace");
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
     * Displays the widget configuration page.
     *
     * @return Response
     */
    public function desktopWidgetPropertiesAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $configs = $this->get('claroline.widget.manager')
            ->generateDesktopDisplayConfig($user->getId());

        return $this->render(
            'ClarolineCoreBundle:Tool\desktop\parameters:widget_properties.html.twig',
            array('configs' => $configs, 'user' => $user)
        );
    }

    /**
     * Inverts the visibility boolean for a widget for the current user.
     *
     * @param integer $widgetId        the widget id
     * @param integer $displayConfigId the display config id (the configuration entity for widgets)
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopInvertVisibleUserWidgetAction($widgetId, $displayConfigId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $this->get('security.context')->getToken()->getUser();
        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->find($widgetId);
        $displayConfig = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('user' => $user, 'widget' => $widget));

        if ($displayConfig == null) {
            $displayConfig = new DisplayConfig();
            $baseConfig = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
                ->find($displayConfigId);
            $displayConfig->setParent($baseConfig);
            $displayConfig->setWidget($widget);
            $displayConfig->setUser($user);
            $displayConfig->setVisible($baseConfig->isVisible());
            $displayConfig->setLock(true);
            $displayConfig->setDesktop(true);
            $displayConfig->invertVisible();
        } else {
            $displayConfig->invertVisible();
        }

        $em->persist($displayConfig);
        $em->flush();

        return new Response('success');
    }

    /**
     * Asks a widget to display its configuration page.
     *
     * @param integer $widgetId the widget id
     *
     * @return Response
     */
    public function desktopConfigureWidgetAction($widgetId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->find($widgetId);
        $event = new ConfigureWidgetDesktopEvent($user);
        $eventName = strtolower("widget_{$widget->getName()}_configuration_desktop");
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if ($event->getContent() !== '') {
            return $this->render(
                'ClarolineCoreBundle:Tool\desktop\parameters:widget_configuration.html.twig',
                array('content' => $event->getContent())
            );
        }

        throw new \Exception("event $eventName didn't return any Response");
    }

    /**
     * Displays the tools configuration page.
     *
     * @return Response
     */
    public function desktopConfigureToolAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $orderedToolList = array();
        $desktopTools = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')->findBy(array('user' => $user));

        foreach ($desktopTools as $desktopTool) {
            $desktopTool->getTool()->setVisible(true);
            $orderedToolList[$desktopTool->getOrder()] = $desktopTool->getTool();
        }

        $undisplayedTools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findByUser($user, false);

        foreach ($undisplayedTools as $tool) {
            $tool->setVisible(false);
        }

        $tools = $this->arrayFill($orderedToolList, $undisplayedTools);

        return $this->render(
            'ClarolineCoreBundle:Tool\desktop\parameters:tool_properties.html.twig',
            array('tools' => $tools)
        );
    }

    /**
     * Remove a tool from the desktop.
     *
     * @param integer $toolId
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function desktopRemoveToolAction($toolId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $desktopTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
            ->findOneBy(array('user' => $user, 'tool' => $toolId));
        $em->remove($desktopTool);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * Add a tool to the desktop.
     *
     * @param integer $toolId
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function desktopAddToolAction($toolId, $position)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);
        $user = $this->get('security.context')->getToken()->getUser();
        $switchTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
            ->findOneBy(array('user' => $user, 'order' => $position));
        if ($switchTool != null) {
            throw new \RuntimeException('A tool already exists at this position');
        }
        $desktopTool = new DesktopTool();
        $desktopTool->setUser($user);
        $desktopTool->setTool($tool);
        $desktopTool->setOrder($position);
        $em->persist($desktopTool);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * This method switch the position of a tool with an other one.
     *
     * @param integer $toolId
     * @param integer $position
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopMoveToolAction($toolId, $position)
    {
         $em = $this->get('doctrine.orm.entity_manager');
         $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);
         $user = $this->get('security.context')->getToken()->getUser();
         $movingTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
            ->findOneBy(array('user' => $user, 'tool' => $tool));
         $switchTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
            ->findOneBy(array('user' => $user, 'order' => $position));

        //if a tool is already at this position, he must go "far away"
        if ($switchTool !== null) {
            //go far away ! Integrety constraints.
            $switchTool->setOrder('99');
            $em->persist($switchTool);
        }

        $em->flush();

        //the tool must exists
        if ($movingTool !== null) {
            $newPosition = $movingTool->getOrder();
            $movingTool->setOrder(intval($position));
            $em->persist($movingTool);
        }

         //put the original tool back.
        if ($switchTool !== null) {
            $switchTool->setOrder($newPosition);
            $em->persist($switchTool);
        }

        $em->flush();

        return new Response('<body>success</body>');
    }

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
        $tools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findBy(array('isDisplayableInWorkspace' => true));
        $wot = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findBy(array('workspace' => $workspaceId));

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
            $toolsPermissions[$orderedTool->getOrder()] = array(
                'tool' => $orderedTool->getTool(),
                'visibility' => $roleVisibility
            );
        }

        $undisplayedTools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findByWorkspace($workspace, false);

        //this is the missing part of the array
        $toFill = array();
        foreach ($undisplayedTools as $undisplayedTool) {
            foreach ($wsRoles as $role) {
                $roleVisibility[$role->getId()] = false;
            }
            $toFill[] = array('tool' => $undisplayedTool, 'visibility' => $roleVisibility);
        }

        $toolsPermissions = $this->arrayFill($toolsPermissions, $toFill);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:tool_roles.html.twig',
            array(
                'roles' => $wsRoles,
                'workspace' => $workspace,
                'workspaceTools' => $tools,
                'toolPermissions' => $toolsPermissions
            )
        );
    }

    public function workspaceToolsParametersAction($workspaceId, $roleName)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $role = $em->getRepository('ClarolineCoreBundle:Role')->findOneBy(array('name' => $roleName));

        if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
        }

        $workspaceTools = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole')
            ->findBy(array('workspace' => $workspace, 'role' => $role));

        $orderedToolList = array();

        foreach ($workspaceTools as $workspaceTool) {
            $workspaceTool->getTool()->setVisible(true);
            $orderedToolList[$workspaceTool->getOrder()] = $workspaceTool->getTool();
        }

        $undisplayedTools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findByRolesAndWorkspace(array($roleName), $workspace, false);

        foreach ($undisplayedTools as $tool) {
            $tool->setVisible(false);
        }

        $tools = $tools = $this->arrayFill($orderedToolList, $undisplayedTools);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:tool_parameters.html.twig',
            array('tools' => $tools, 'workspace' => $workspace, 'role' => $role)
        );
    }

    /**
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

        if ($wot === null) {
            $wot = new WorkspaceOrderedTool();
            $wot->setOrder($position);
            $wot->setTool($em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId));
            $wot->setWorkspace($workspace);
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
            $config = $this->get('claroline.workspace.exporter')->export($workspace);
            $config['name'] = $name;
            $yaml = Yaml::dump($config, 10);
            $ds = DIRECTORY_SEPARATOR;
            file_put_contents(
                $this->container->getParameter('kernel.root_dir')."{$ds}..{$ds}workspaces{$ds}{$name}.yml", $yaml
            );
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
     * Fill the empty value on $fillable with $array and sort it.
     *
     * Ie:
     * $fillable[4] = value4
     * $fillable[1] = value1
     * $fillable[2] = value2
     *
     * $array[] = value3
     *
     * One the function is fired the results is
     * $fillable[1] = value1
     * $fillable[2] = value2
     * $fillable[3] = value3
     * $fillable[4] = value4
     *
     * @param array $fillable
     * @param array $array
     *
     * @return array
     */
    public function arrayFill(array $fillable, array $array)
    {
        ksort($fillable);
        $saveKey = 1;
        $filledArray = array();

        foreach ($fillable as $key => $value) {
            if ($key - $saveKey != 0) {
                while ($key - $saveKey >= 1) {
                    $filledArray[$saveKey] = array_shift($array);
                    $saveKey++;
                }
                $filledArray[$key] = $value;
            } else {
                $filledArray[$key] = $value;
            }
            $saveKey++;
        }

        if (count($array) > 0) {
            foreach ($array as $item) {
                $filledArray[] = $item;
            }
        }

        return $filledArray;

    }

    public function workspaceEditFormAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        $form = $this->createForm(new WorkspaceEditType(), $workspace);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:workspace_edit.html.twig',
            array('form' => $form->createView(),
                  'workspace' => $workspace)
        );
    }

    public function workspaceEditAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
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
        }

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:workspace_edit.html.twig',
            array('form' => $form->createView(),
                  'workspace' => $workspace)
        );
    }
}

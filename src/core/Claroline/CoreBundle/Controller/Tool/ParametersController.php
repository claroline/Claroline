<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Tool\DesktopTool;
use Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole;
use Claroline\CoreBundle\Library\Widget\Event\ConfigureWidgetWorkspaceEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Response;

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
            'ClarolineCoreBundle:Tool:workspace\parameters\widget_properties.html.twig',
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
                'ClarolineCoreBundle:Tool:workspace\parameters\widget_configuration.html.twig',
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

        $tools = $this->claroArrayFill($orderedToolList, $undisplayedTools);

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

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:tool_roles.html.twig',
            array('roles' => $wsRoles, 'workspace' => $workspace)
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

        $tools = $tools = $this->claroArrayFill($orderedToolList, $undisplayedTools);

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

        $wtr = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole')
            ->findOneBy(array('role' => $roleId, 'tool' => $toolId, 'workspace' => $workspaceId));
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
        $switchTool = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole')
            ->findOneBy(array('workspace' => $workspace, 'order' => $position, 'role' => $role));

        if ($switchTool != null) {
            throw new \RuntimeException('A tool already exists at this position');
        }

        $wtr = new WorkspaceToolRole();
        $wtr->setRole($role);
        $wtr->setTool($em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId));
        $wtr->setWorkspace($workspace);
        $wtr->setOrder($position);
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
    public function workspaceMoveToolAction($toolId, $position, $workspaceId, $roleId)
    {
         $em = $this->get('doctrine.orm.entity_manager');
         $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

         if (!$this->get('security.context')->isGranted('parameters', $workspace)) {
            throw new AccessDeniedHttpException();
         }

         $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);
         $role = $em->getRepository('ClarolineCoreBundle:Role')->find($roleId);

         $movingTool = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole')
            ->findOneBy(array('role' => $role, 'tool' => $tool, 'workspace' => $workspace));
         $switchTool = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole')
            ->findOneBy(array('role' => $role, 'order' => $position, 'workspace' => $workspace));

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

    public function claroArrayFill(array $fillable, array $array)
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
}


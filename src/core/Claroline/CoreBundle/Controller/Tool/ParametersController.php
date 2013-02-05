<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Tool\DesktopTool;
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
     * Renders the workspace roles configuration page.
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceConfigureRightsAction($workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);

        return $this->render(
            'ClarolineCoreBundle:Tool:workspace\parameters\rights_list.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * Renders the resource configuration for a specific role.
     *
     * @param integer $roleId
     *
     * @return Response
     */
    public function workspaceRightsFormAction($workspaceId)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
            ->find($workspaceId);
        $configs = $em->getRepository('ClarolineCoreBundle:Rights\WorkspaceRights')
            ->findBy(array('workspace' => $workspaceId));

        return $this->render(
            'ClarolineCoreBundle:Tool:workspace\parameters\workspace_rights.html.twig',
            array('workspace' => $workspace, 'configs' => $configs)
        );
    }

    /**
     * Edit the resources permissions. It handles to form displayed by the
     * workspaceRightsFormAction method. The handling is a bit weird because
     * the form wasn't created with the Symfony2 form component.
     *
     * @param integer $workspaceId
     *
     * @return Response
     */
    public function workspaceEditRightsAction($workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $configs = $em->getRepository('ClarolineCoreBundle:Rights\WorkspaceRights')
            ->findBy(array('workspace' => $workspaceId));
        $checks = $this->get('claroline.security.utilities')
            ->setRightsRequest($this->get('request')->request->all(), 'workspace');

        foreach ($configs as $config) {
            $config->reset();

            if (isset($checks[$config->getId()])) {
                $config->setRights($checks[$config->getId()]);
                if ($config->getRole()->getName() == 'ROLE_ANONYMOUS') {
                    //if anonymous can see a a workspace, he also can see the root
                    if ($checks[$config->getId()]['canView'] === true) {
                        $ws = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')
                            ->find($workspaceId);
                        $root = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                            ->getRootForWorkspace($ws);
                        $role = $em->getRepository('ClarolineCoreBundle:Role')
                            ->findOneBy(array('name' => 'ROLE_ANONYMOUS'));
                        $resourceRight = $em->getRepository('ClarolineCoreBundle:Resource\ResourceContext')
                            ->findOneBy(array('resource' => $root, 'role' => $role));
                        $resourceRight->setCanOpen(true);
                        $em->persist($resourceRight);
                    }
                }
            }

            $em->persist($config);
        }

        $em->flush();

        return $this->redirect(
            $this->generateUrl(
                'claro_workspace_rights',
                array('workspaceId' => $workspaceId)
            )
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
        $displayedTools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->getDesktopTools($user);

        foreach ($displayedTools as $tool) {
            $tool->setVisible(true);
        }

        $undisplayedTools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->getDesktopUndisplayedTools($user);

        foreach ($undisplayedTools as $tool) {
            $tool->setVisible(false);
        }

        $tools = array_merge($displayedTools, $undisplayedTools);

        return $this->render(
            'ClarolineCoreBundle:Tool\desktop\parameters:tool_properties.html.twig',
            array('tools' => $tools)
        );
    }

    public function desktopInvertToolVisibilityAction($toolId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);
        $user = $this->get('security.context')->getToken()->getUser();
        $displayedTools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->getDesktopTools($user);
        $found = false;

        foreach ($displayedTools as $displayedTool) {
            if ($tool == $displayedTool) {
                $found = true;
                if ($tool->isDesktopRequired()) {
                    throw new \Exception('this tool is required in the desktop');
                } else {
                    $desktopTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
                        ->findOneBy(array('user' => $user, 'tool' => $tool));
                    $em->remove($desktopTool);

                    foreach ($displayedTools as $remainingTool) {
                        $remainingDesktopTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
                            ->findOneBy(array('user' => $user, 'tool' => $tool));
                        $remainingDesktopTool->moveUp();
                        $em->persist($remainingDesktopTool);
                    }

                    $em->flush();

                    return new Response('success', 204);
                }
            }
        }

        $totalTools = count($displayedTools);
        $totalTools++;
        $desktopTool = new DesktopTool();
        $desktopTool->setUser($user);
        $desktopTool->setTool($tool);
        $desktopTool->setOrder($totalTools);
        $em->persist($desktopTool);
        $em->flush();

        return new Response('success', 204);
    }

    public function desktopMoveToolUpAction($toolId)
    {
         $em = $this->get('doctrine.orm.entity_manager');
         $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->find($toolId);
         $user = $this->get('security.context')->getToken()->getUser();
         $displayedTools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->getDesktopTools($user);

         foreach ($displayedTools as $displayedTool) {
             if ($tool == $displayedTool) {
                 $desktopTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
                      ->findOneBy(array('user' => $user, 'tool' => $tool));

                 if ($desktopTool->getOrder() === 1) {
                     throw new \RuntimeException('this row is already the first and cannot be moved up');
                 }

                 $switchOrder = $desktopTool->getOrder();
                 $switchOrder--;
                 $switchTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
                      ->findOneBy(array('order' => $switchOrder, 'user' => $user));
                 $desktopTool->moveUp();
                 $switchTool->moveDown();
                 $em->persist($desktopTool);
                 $em->persist($switchTool);
                 $em->flush();

                 return new Response('success');
             }
        }

        throw new \RuntimeException("this tool isn't visible yet");
    }
}


<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Controller\Tool\AbstractParametersController;
use Claroline\CoreBundle\Library\Event\ConfigureWidgetWorkspaceEvent;

class WorkspaceWidgetParametersController extends AbstractParametersController
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
        $this->checkAccess($workspace);
        $configs = $this->get('claroline.widget.manager')
            ->generateWorkspaceDisplayConfig($workspaceId);

        return $this->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:widget_properties.html.twig',
            array('workspace' => $workspace, 'configs' => $configs, 'tool' => $this->getHomeTool())
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
        $this->checkAccess($workspace);
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
        $this->checkAccess($workspace);
        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->find($widgetId);
        $event = new ConfigureWidgetWorkspaceEvent($workspace);
        $eventName = "widget_{$widget->getName()}_configuration_workspace";
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if ($event->getContent() !== '') {
            return $this->render(
                'ClarolineCoreBundle:Tool\workspace\parameters:widget_configuration.html.twig',
                array('content' => $event->getContent(), 'workspace' => $workspace, 'tool' => $this->getHomeTool())
            );
        }

        throw new \Exception("event {$eventName} didn't return any Response");
    }

    private function getHomeTool()
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneByName('home');
    }
}
<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\Tool\DesktopTool;
use Claroline\CoreBundle\Library\Event\ConfigureWidgetDesktopEvent;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DesktopParametersController extends Controller
{
    /**
     * @Route(
     *     "/widget/properties",
     *     name="claro_widget_properties"
     * )
     *
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
     * @Route(
     *     "/config/{displayConfigId}/widget/{widgetId}/invertvisible",
     *     name="claro_desktop_widget_invertvisible",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * Inverts the visibility boolean for a widget for the current user.
     *
     * @param integer $widgetId        the widget id
     * @param integer $displayConfigId the display config id (the configuration entity for widgets)
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopInvertVisibleUserWidgetAction($widgetId, $displayConfigId)
    {
        $em = $this->getDoctrine()->getManager();
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
     * @Route(
     *     "widget/{widgetId}/configuration/desktop",
     *     name="claro_desktop_widget_configuration",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
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
        $eventName = "widget_{$widget->getName()}_configuration_desktop";
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
     * @Route(
     *     "/tools",
     *     name="claro_tool_properties"
     * )
     *
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

        $tools = $this->get('claroline.utilities.misc')->arrayFill($orderedToolList, $undisplayedTools);

        return $this->render(
            'ClarolineCoreBundle:Tool\desktop\parameters:tool_properties.html.twig',
            array('tools' => $tools)
        );
    }

    /**
     * @Route(
     *     "/remove/tool/{toolId}",
     *     name="claro_tool_desktop_remove",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * Remove a tool from the desktop.
     *
     * @param integer $toolId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function desktopRemoveToolAction($toolId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $tool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->find($toolId);
        if ($tool->getName() === 'parameters') {
            throw new \Exception('You cannot remove the parameter tool from the desktop.');
        }
        $desktopTool = $em->getRepository('ClarolineCoreBundle:Tool\DesktopTool')
            ->findOneBy(array('user' => $user, 'tool' => $toolId));
        $em->remove($desktopTool);
        $em->flush();

        return new Response('success', 204);
    }

    /**
     * @Route(
     *     "/add/tool/{toolId}/position/{position}",
     *     name="claro_tool_desktop_add",
     *     options={"expose"=true}
     * )
     * @Method("POST")
     *
     * Add a tool to the desktop.
     *
     * @param integer $toolId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
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
     * @Route(
     *     "/move/tool/{toolId}/position/{position}",
     *     name="claro_tool_desktop_move",
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
}
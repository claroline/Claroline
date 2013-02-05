<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Tool\Event\DisplayToolEvent;

/**
 * Controller of the user's desktop.
 */
class DesktopController extends Controller
{
    /**
     * Displays registered widgets.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function widgetsAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $configs = $this->get('claroline.widget.manager')
            ->generateDesktopDisplayConfig($user->getId());

        //The line below is some weird doctrine optimization. Widgets are loaded one by one otherwise.
        $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findAll();

        foreach ($configs as $config) {
            if ($config->isVisible()) {
                $eventName = strtolower("widget_{$config->getWidget()->getName()}_desktop");
                $event = new DisplayWidgetEvent();
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
     * Renders the left tool bar. Not routed.
     *
     * @return Response
     */
    public function renderToolListAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $tools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->getDesktopTools($user);

        return $this->render(
            'ClarolineCoreBundle:Desktop:tool_list.html.twig',
            array('tools' => $tools)
        );
    }

    /**
     * Opens a tool.
     *
     * @param string $toolName
     *
     * @return Response
     */
    public function openToolAction($toolName)
    {
        $event = new DisplayToolEvent();
        $eventName = 'open_tool_desktop_'.$toolName;
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        return new Response($event->getContent());
    }
}
<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Event\DisplayWidgetEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller of the user's desktop.
 */
class DesktopController extends Controller
{
    /**
     * @Route(
     *     "/widgets",
     *     name="claro_desktop_widgets"
     * )
     *
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
                $eventName = "widget_{$config->getWidget()->getName()}_desktop";
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
        $tools = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findByUser($user, true);

        return $this->render(
            'ClarolineCoreBundle:Desktop:tool_list.html.twig',
            array('tools' => $tools)
        );
    }

    /**
     * @Route(
     *     "tool/open/{toolName}",
     *     name="claro_desktop_open_tool",
     *     options={"expose"=true}
     * )
     *
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

        if (is_null($event->getContent())) {
            throw new \Exception(
                "Tool '{$toolName}' didn't return any Response for tool event '{$eventName}'."
            );
        }

        return new Response($event->getContent());
    }

    /**
     * @Route(
     *     "/open",
     *     name="claro_desktop_open"
     * )
     *
     * Opens the desktop.
     *
     * @return Response
     */
    public function openAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $openedTool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findByUser($this->get('security.context')->getToken()->getUser(), true);

        $route = $this->get('router')->generate(
            'claro_desktop_open_tool',
            array('toolName' => $openedTool[0]->getName())
        );

        return new RedirectResponse($route);
    }
}
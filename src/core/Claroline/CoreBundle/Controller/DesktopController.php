<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Event\Event\DisplayWidgetEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Claroline\CoreBundle\Event\Event\DisplayToolEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * Controller of the user's desktop.
 */
class DesktopController extends Controller
{
    /**
     * @EXT\Route(
     *     "/widgets",
     *     name="claro_desktop_widgets"
     * )
     * @EXT\Template("ClarolineCoreBundle:Widget:widgets.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Displays registered widgets.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function widgetsAction(User $user)
    {
        $configs = $this->get('claroline.widget.manager')
            ->generateDesktopDisplayConfig($user->getId());

        //The line below is some weird doctrine optimization. Widgets are loaded one by one otherwise.
        $this->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findAll();

        $widgets = array();

        //TODO get Parameters' Tool configuration

        foreach ($configs as $config) {
            if ($config->isVisible()) {
                $event = $this->get('claroline.event.event_dispatcher')->dispatch(
                    "widget_{$config->getWidget()->getName()}_desktop",
                    'DisplayWidget'
                );

                if ($event->hasContent()) {
                    $widget['id'] = $config->getWidget()->getId();
                    if ($event->hasTitle()) {
                        $widget['title'] = $event->getTitle();
                    } else {
                        $widget['title'] = strtolower($config->getWidget()->getName());
                    }
                    $widget['content'] = $event->getContent();
                    $widget['configurable'] = ($config->isLocked() !== true and $config->getWidget()->isConfigurable());

                    $widgets[] = $widget;
                }
            }
        }

        return array(
            'widgets' => $widgets,
            'isDesktop' => true
        );
    }

    /**
     * @EXT\Template()
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Renders the left tool bar. Not routed.
     *
     * @return Response
     */
    public function renderToolListAction(User $user)
    {
        return array('tools' => $this->get('claroline.manager.tool_manager')->getDisplayedDesktopOrderedTools($user));
    }

    /**
     * @EXT\Route(
     *     "tool/open/{toolName}",
     *     name="claro_desktop_open_tool",
     *     options={"expose"=true}
     * )
     *
     * Opens a tool.
     *
     * @param string $toolName
     *
     * @throws \Exception
     * @return Response
     */
    public function openToolAction($toolName)
    {
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            'open_tool_desktop_'.$toolName,
            'DisplayTool'
        );

        return new Response($event->getContent());
    }

    /**
     * @EXT\Route(
     *     "/open",
     *     name="claro_desktop_open"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * Opens the desktop.
     *
     * @return Response
     */
    public function openAction(User $user)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $openedTool = $em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findDesktopDisplayedToolsByUser($user);

        $route = $this->get('router')->generate(
            'claro_desktop_open_tool',
            array('toolName' => $openedTool[0]->getName())
        );

        return new RedirectResponse($route);
    }
}
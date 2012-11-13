<?php
namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Widget\Event\ConfigureWidgetWorkspaceEvent;
use Claroline\CoreBundle\Library\Widget\Event\ConfigureWidgetDesktopEvent;

class AdministrationWidgetController extends Controller
{

    /**
     *  Display the list of widget options for the administrator
     *
     * @return Response
     */
    public function widgetListAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $wconfigs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('parent' => null, 'isDesktop' => false));
        $dconfigs = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->findBy(array('parent' => null, 'isDesktop' => true));

        return $this->render('ClarolineCoreBundle:Administration:widgets.html.twig',
            array('wconfigs' => $wconfigs, 'dconfigs' => $dconfigs));
    }

    /**
     * Set true|false to the widget displayConfig isLockedByAdmin option
     *
     * @param integer $displayConfigId
     */
    public function invertLockWidgetAction($displayConfigId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $config = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->find($displayConfigId);
        $config->invertLock();
        $em->persist($config);
        $em->flush();

        return new Response('success', 204);
    }

    public function configureWorkspaceWidgetAction($widgetId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')->find($widgetId);
        $event = new ConfigureWidgetWorkspaceEvent(null, true);
        $eventName = strtolower("widget_{$widget->getName()}_configuration_workspace");
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if ($event->getContent() !== '') {
            return $this->render('ClarolineCoreBundle:Administration:widget_configuration.html.twig', array('content' => $event->getContent()));
        } else {
            throw new \Exception("event $eventName didn't return any Response");
        }
    }

    public function configureDesktopWidgetAction($widgetId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')->find($widgetId);
        $event = new ConfigureWidgetDesktopEvent(null, true);
        $eventName = strtolower("widget_{$widget->getName()}_configuration_desktop");
        $this->get('event_dispatcher')->dispatch($eventName, $event);

        if ($event->getContent() !== '') {
            return $this->render('ClarolineCoreBundle:Administration:widget_configuration.html.twig', array('content' => $event->getContent()));
        } else {
            throw new \Exception("event $eventName didn't return any Response");
        }
    }

    /**
     *  Set true|false to the widget displayConfig isVisible option
     *
     * @param integer $displayConfigId
     */
    public function invertVisibleWidgetAction($displayConfigId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $config = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')->find($displayConfigId);
        $config->invertVisible();
        $em->persist($config);
        $em->flush();

        return new Response('success', 204);
    }
}
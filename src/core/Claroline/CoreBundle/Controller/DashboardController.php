<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Plugin\Event\DisplayWidgetEvent;

/**
 * Controller of the user's dashboard.
 */
class DashboardController extends Controller
{
    /**
     * Displays the dashboard index.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        // There is no real "index" page, it is usually the "information" tab
        // (in the future, this could be set by the administrator)
        return $this->redirect($this->generateUrl('claro_dashboard_info'));
    }

    /**
     * Displays the Info dashboard tab.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoAction()
    {
        return $this->render('ClarolineCoreBundle:Dashboard:info.html.twig');
    }

    /**
     * Displays the Perso dashboard tab.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function persoAction()
    {
        return $this->render('ClarolineCoreBundle:Dashboard:perso.html.twig');
    }

    /**
     * Displays the resource manager.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resourceManagerAction()
    {
        $resourceTypes = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findBy(array('isVisible' => 1));

        return $this->render('ClarolineCoreBundle:Dashboard:resources.html.twig', array('resourceTypes' => $resourceTypes));
    }

    /**
     * Display a log widget
     * //todo create a database widget and use wigetsAction instead
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resourceLogsAction()
    {
        $logs = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Logger\ResourceLogger')->getLastLogs($this->get('security.context')->getToken()->getUser());
        return $this->render('ClarolineCoreBundle:Dashboard:widgets\resource_events.html.twig', array('logs' => $logs));
    }

    /**
     * Display registered widgets
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function widgetsAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $widgets = $em->getRepository('Claroline\CoreBundle\Entity\Widget\Widget')->findAll();

        foreach ($widgets as $widget){
            $eventName = strtolower("widget_{$widget->getName()}_dashboard");
            $event = new DisplayWidgetEvent();
            $this->get('event_dispatcher')->dispatch($eventName, $event);
            $responsesString[strtolower($widget->getName())] = $event->getContent();
        }

        return $this->render('ClarolineCoreBundle:Dashboard:widgets\plugins.html.twig', array('widgets' => $responsesString));
    }
}
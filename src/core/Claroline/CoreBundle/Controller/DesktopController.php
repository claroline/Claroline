<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;

/**
 * Controller of the user's desktop.
 */
class DesktopController extends Controller
{
    /**
     * Displays the desktop index.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        // There is no real "index" page, it is usually the "information" tab
        // (in the future, this could be set by the administrator)
        return $this->redirect($this->generateUrl('claro_desktop_info'));
    }

    /**
     * Displays the Info desktop tab.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function infoAction()
    {
        return $this->render('ClarolineCoreBundle:Desktop:info.html.twig');
    }

    /**
     * Displays the Perso desktop tab.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function persoAction()
    {
        return $this->render('ClarolineCoreBundle:Desktop:perso.html.twig');
    }

    /**
     * Displays the resource manager.
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resourceManagerAction()
    {
        $resourceTypes = $this->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findBy(array('isVisible' => true));

        return $this->render(
            'ClarolineCoreBundle:Desktop:resources.html.twig',
            array('resourceTypes' => $resourceTypes)
        );
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
            $eventName = strtolower("widget_{$widget->getName()}");
            $event = new DisplayWidgetEvent();
            $this->get('event_dispatcher')->dispatch($eventName, $event);
            $responsesString[strtolower($widget->getName())] = $event->getContent();
        }

        return $this->render('ClarolineCoreBundle:Widget:widgets.html.twig', array('widgets' => $responsesString));
    }
}
<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Library\Widget\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Library\Widget\Event\ConfigureWidgetDesktopEvent;

/**
 * Controller of the user's desktop.
 */
class DesktopController extends Controller
{
    /**
     * Displays the desktop index.
     *
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
     *
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
     * Displays registered widgets.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function widgetsAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $configs = $this->get('claroline.widget.manager')
            ->generateDesktopDisplayConfig($user->getId());

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
     * Displays the user parameters page for its desktop.
     *
     * @return Response
     */
    public function desktopUserParametersAction()
    {
        return $this->render('ClarolineCoreBundle:Desktop:user_parameters.html.twig');
    }

    /**
     * Displays the widget configuration page.
     *
     * @return Response
     */
    public function widgetPropertiesAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $configs = $this->get('claroline.widget.manager')
            ->generateDesktopDisplayConfig($user->getId());

        return $this->render(
            'ClarolineCoreBundle:Desktop:widget_properties.html.twig',
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
    public function invertVisibleUserWidgetAction($widgetId, $displayConfigId)
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
    public function configureWidgetAction($widgetId)
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
                'ClarolineCoreBundle:Desktop:widget_configuration.html.twig',
                array('content' => $event->getContent())
            );
        }

        throw new \Exception("event $eventName didn't return any Response");
    }
}
<?php

/*
 * This file is part of the Inwicast plugin for Claroline Connect.
 *
 * (c) INWICAST <dev@inwicast.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icap\InwicastBundle\Listener;

use Claroline\CoreBundle\Event\ConfigureWidgetEvent;
use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\DisplayWidgetEvent;
use Claroline\CoreBundle\Event\InjectJavascriptEvent;
use Icap\InwicastBundle\Entity\Media;
use Icap\InwicastBundle\Entity\MediaCenter;
use Icap\InwicastBundle\Entity\MediacenterUser;
use Icap\InwicastBundle\Exception\NoMediacenterException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @DI\Service
 */
class ClarolinePluginListener extends ContainerAware
{
    private $templating;

    //-------------------------------
    // PLUGIN GENERAL SETTINGS
    //-------------------------------

    /**
     * @DI\Observe("inject_javascript_layout")
     *
     * @param InjectJavascriptEvent $event
     *
     * @return string
     */
    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $content = $this->templating->render(
            'IcapInwicastBundle:Inwicast:javascript_layout.html.twig',
            []
        );

        $event->addContent($content);
    }

    /**
     * @DI\InjectParams({
     *      "container"             = @DI\Inject("service_container")
     * })
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->setContainer($container);
        $this->templating = $container->get('templating');
    }

    //-------------------------------
    // WIDGET SERVICES
    //-------------------------------

    /**
     * @DI\Observe("widget_inwicast_claroline_plugin")
     *
     * @param DisplayWidgetEvent $event
     */
    public function onDisplay(DisplayWidgetEvent $event)
    {
        // Get the Media entity from event
        $widgetInstance = $event->getInstance();
        $mediaManager = $this->getMediaManager();
        $media = $mediaManager->getByWidget($widgetInstance);
        if (!empty($media)) {
            try {
                $mediacenter = $this->getMediacenterManager()->getMediacenter();
                //$loggedUser = $this->container->get("security.context")->getToken()->getUser();
                //$media = $mediaManager->getMediaInfo($media, $mediacenter, $loggedUser);
                // Get video player
                $event->setContent(
                    $this->templating->render(
                        'IcapInwicastBundle:Media:view.html.twig',
                        ['media' => $media, 'mediacenter' => $mediacenter]
                    )
                );
            } catch (NoMediacenterException $nme) {
                $event->setContent(
                    $this->templating->render(
                        'IcapInwicastBundle:MediaCenter:error.html.twig'
                    )
                );
            }
        } else {
            $event->setContent(
                $this->templating->render(
                    'IcapInwicastBundle:Media:noMedia.html.twig'
                )
            );
        }

        $event->stopPropagation();
    }

    /**
     * @DI\Observe("widget_inwicast_claroline_plugin_configuration")
     */
    public function onConfigure(ConfigureWidgetEvent $event)
    {
        // Get widget instance
        $widgetInstance = $event->getInstance();
        // Get mediacenter user from database
        $loggedUser = $this->container->get('security.context')->getToken()->getUser();
        try {
            $mediacenter = $this->getMediacenterManager()->getMediacenter();
            $mediaManager = $this->getMediaManager();
            $medialist = $mediaManager->getMediaListForUser($loggedUser, $mediacenter);
            // Return form
            $content = $this->templating->render(
                'IcapInwicastBundle:Media:videosList.html.twig',
                [
                    'medialist' => $medialist,
                    'widget' => $widgetInstance,
                    'username' => $loggedUser->getUsername(),
                    'mediacenter' => $mediacenter,
                ]
            );
        } catch (NoMediacenterException $nme) {
            $content = $this->templating->render('IcapInwicastBundle:MediaCenter:error.html.twig');
        }

        // Return view to event (Claroline specification)
        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_desktop_inwicast_portal")
     */
    public function onToolOpen(DisplayToolEvent $event)
    {
        // Get mediacenter user from database
        $loggedUser = $this->container->get('security.context')->getToken()->getUser();
        try {
            $mediacenter = $this->getMediacenterManager()->getMediacenter();
            $mediacenterUserManager = $this->getMediacenterUserManager();
            $token = $mediacenterUserManager->getMediacenterUserToken($loggedUser, $mediacenter);
            $mediacener_portal = $mediacenter->getUrl().'?userName='.$loggedUser->getUsername().'&token='.$token;
            $content = new RedirectResponse($mediacener_portal);
        } catch (NoMediacenterException $nme) {
            $content = $this->templating->render('IcapInwicastBundle:MediaCenter:error.html.twig');
        }

        // Return view to event (Claroline specification)
        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @return \Icap\InwicastBundle\Manager\MediaCenterManager
     */
    private function getMediacenterManager()
    {
        return $this->container->get('inwicast.plugin.manager.mediacenter');
    }

    /**
     * @return \Icap\InwicastBundle\Manager\MediaCenterUserManager
     */
    private function getMediacenterUserManager()
    {
        return $this->container->get('inwicast.plugin.manager.mediacenteruser');
    }

    /**
     * @return \Icap\InwicastBundle\Manager\MediaManager
     */
    private function getMediaManager()
    {
        return $this->container->get('inwicast.plugin.manager.media');
    }
}

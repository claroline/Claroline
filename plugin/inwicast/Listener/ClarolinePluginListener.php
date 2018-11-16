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

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Icap\InwicastBundle\Exception\NoMediacenterException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @DI\Service
 */
class ClarolinePluginListener
{
    use ContainerAwareTrait;

    private $templating;

    /**
     * ClarolinePluginListener constructor.
     *
     * @DI\InjectParams({
     *      "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function __construct(
        ContainerInterface $container
    ) {
        $this->setContainer($container);
        $this->templating = $container->get('templating');
    }

    /**
     * @DI\Observe("layout.inject.javascript")
     *
     * @param InjectJavascriptEvent $event
     *
     * @return string
     */
    public function onInjectJs(InjectJavascriptEvent $event)
    {
        $event->addContent(
            $this->templating->render('IcapInwicastBundle:inwicast:javascript_layout.html.twig')
        );
    }

    /**
     * @DI\Observe("open_tool_desktop_inwicast_portal")
     *
     * @param DisplayToolEvent $event
     */
    public function onToolOpen(DisplayToolEvent $event)
    {
        // Get media center user from database
        $loggedUser = $this->container->get('security.token_storage')->getToken()->getUser();
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
}

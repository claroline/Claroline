<?php

namespace Icap\OAuthBundle\Listener;

use Claroline\CoreBundle\Event\RenderAuthenticationButtonEvent;
use Claroline\CoreBundle\Event\InjectJavascriptEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class ExternalAuthenticationListener
{
    private $templating;
    private $oauthManager;

    /**
     * @DI\InjectParams({
     *     "templating"      = @DI\Inject("templating"),
     *     "oauthManager" = @Di\Inject("icap.oauth.manager")
     * })
     */
    public function __construct($templating, $oauthManager)
    {
        $this->templating = $templating;
        $this->oauthManager = $oauthManager;
    }

    /**
     * @DI\Observe("render_external_authentication_button")
     *
     * @param RenderAuthenticationButtonEvent $event
     * @return string
     */
    public function onRenderButton(RenderAuthenticationButtonEvent $event)
    {
        $services = $this->oauthManager->getActiveServices();
        if (count($services)>0) {
            $content = $this->templating->render(
                'IcapOAuthBundle::buttons.html.twig',
                array('services'=>$services)
            );

            $event->addContent($content);
        }
    }


}

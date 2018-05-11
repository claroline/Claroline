<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Event\RenderAuthenticationButtonEvent;
use Claroline\CoreBundle\Manager\OauthManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 */
class LoginPageAuthenticationListener
{
    private $templating;
    private $oauthManager;

    /**
     * @DI\InjectParams({
     *     "templating"      = @DI\Inject("templating"),
     *     "oauthManager" = @DI\Inject("claroline.manager.oauth_manager")
     * })
     */
    public function __construct($templating, OauthManager $oauthManager)
    {
        $this->templating = $templating;
        $this->oauthManager = $oauthManager;
    }

    /**
     * @DI\Observe("render_external_authentication_button")
     *
     * @param RenderAuthenticationButtonEvent $event
     *
     * @return string
     */
    public function onRenderButton(RenderAuthenticationButtonEvent $event)
    {
        $platforms = $this->oauthManager->findActivatedExternalAuthentications();
        $content = $this->templating->render(
            'ClarolineCoreBundle:authentication:external_claroline.html.twig',
            ['platforms' => $platforms]
        );

        $event->addContent($content);
    }
}

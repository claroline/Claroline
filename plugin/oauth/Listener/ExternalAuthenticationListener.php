<?php

namespace Icap\OAuthBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;
use Claroline\CoreBundle\Event\LoginTargetUrlEvent;
use Claroline\CoreBundle\Event\RenderAuthenticationButtonEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service()
 */
class ExternalAuthenticationListener
{
    private $templating;
    private $oauthManager;
    private $router;
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @DI\InjectParams({
     *     "templating"     = @DI\Inject("templating"),
     *     "oauthManager"   = @Di\Inject("icap.oauth.manager"),
     *     "router"         = @Di\Inject("router"),
     *     "translator"     = @Di\Inject("translator")
     * })
     */
    public function __construct($templating, $oauthManager, $router, $translator)
    {
        $this->templating = $templating;
        $this->oauthManager = $oauthManager;
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("render_external_authentication_button", priority=1)
     *
     * @param RenderAuthenticationButtonEvent $event
     *
     * @return string
     */
    public function onRenderButton(RenderAuthenticationButtonEvent $event)
    {
        $services = $this->oauthManager->getActiveServices();
        $buttons = [];

        foreach ($services as $service) {
            $config = $this->oauthManager->getConfiguration($service);
            $buttons[] = ['service' => $service, 'display_name' => $config->getDisplayName()];
        }

        if (count($services) > 0) {
            $content = $this->templating->render(
                'IcapOAuthBundle::buttons.html.twig',
                ['buttons' => $buttons]
            );

            $event->addContent($content);
        }
    }

    /**
     * @DI\Observe("external_login_target_url_event", priority=1)
     *
     * @param LoginTargetUrlEvent $event
     */
    public function onExternalLoginTargetUrl(LoginTargetUrlEvent $event)
    {
        $services = $this->oauthManager->getActiveServices();
        if (count($services) > 0) {
            foreach ($services as $service) {
                $event->addTarget(
                    $this->translator->trans($service, [], 'icap_oauth'),
                    $this->router->generate('hwi_oauth_service_redirect', ['service' => $service])
                );
            }
        }
    }

    /**
     * @DI\Observe("log")
     *
     * @param LogGenericEvent $event
     */
    public function onDeleteUser(LogGenericEvent $event)
    {
        if ($event instanceof LogUserDeleteEvent) {
            $receiver = $event->getReceiver();
            if ($receiver !== null) {
                $this->oauthManager->unlinkAccount($receiver->getId());
            }
        }
    }
}

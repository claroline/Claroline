<?php

namespace Claroline\CasBundle\Listener;

use Claroline\CasBundle\Library\Configuration\CasServerConfiguration;
use Claroline\CasBundle\Library\Configuration\CasServerConfigurationFactory;
use Claroline\CasBundle\Manager\CasManager;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;
use Claroline\CoreBundle\Event\LoginTargetUrlEvent;
use Claroline\CoreBundle\Event\RenderAuthenticationButtonEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\TwigEngine;

/**
 * @DI\Service()
 */
class ExternalAuthenticationListener
{
    /** @var TwigEngine */
    private $templating;
    /** @var CasManager */
    private $casManager;
    /** @var CasServerConfiguration */
    private $casServerConfig;

    /**
     * @DI\InjectParams({
     *     "templating"             = @DI\Inject("templating"),
     *     "casManager"             = @DI\Inject("claroline.manager.cas_manager"),
     *     "casServerConfigFactory" = @DI\Inject("claroline.factory.cas_configuration")
     * })
     */
    public function __construct(
        TwigEngine $templating,
        CasManager $casManager,
        CasServerConfigurationFactory $casServerConfigFactory
    ) {
        $this->templating = $templating;
        $this->casManager = $casManager;
        $this->casServerConfig = $casServerConfigFactory->getCasConfiguration();
    }

    /**
     * @DI\Observe("render_external_authentication_button", priority=3)
     *
     * @param RenderAuthenticationButtonEvent $event
     *
     * @return string
     */
    public function onRenderButton(RenderAuthenticationButtonEvent $event)
    {
        if (
            $this->casServerConfig->isActive() &&
            $this->casServerConfig->getLoginOption() === CasServerConfiguration::DEFAULT_LOGIN
        ) {
            $content = $this->templating->render(
                'ClarolineCasBundle::casLoginButton.html.twig',
                ['name' => $this->casServerConfig->getName()]
            );
            $event->addContent($content);
        }
    }

    /**
     * @DI\Observe("render_primary_external_authentication_button", priority=1)
     *
     * @param RenderAuthenticationButtonEvent $event
     */
    public function onRenderPrimaryButton(RenderAuthenticationButtonEvent $event)
    {
        if (
            $this->casServerConfig->isActive() &&
            $this->casServerConfig->getLoginOption() === CasServerConfiguration::PRIMARY_LOGIN
        ) {
            $content = $this->templating->render(
                'ClarolineCasBundle::casLoginButton.html.twig',
                ['name' => $this->casServerConfig->getName()]
            );
            $event->addContent($content);
        }
    }

    /**
     * @DI\Observe("external_login_target_url_event", priority=3)
     *
     * @param LoginTargetUrlEvent $event
     */
    public function onExternalLoginTargetUrl(LoginTargetUrlEvent $event)
    {
        if ($this->casServerConfig->isActive()) {
            $event->addTarget('CAS', 'claro_cas_security_entry_point');
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
                $this->casManager->unlinkAccount($receiver->getId());
            }
        }
    }
}

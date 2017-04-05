<?php

namespace Claroline\LdapBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;
use Claroline\CoreBundle\Event\LoginTargetUrlEvent;
use Claroline\CoreBundle\Event\RenderAuthenticationButtonEvent;
use Claroline\CoreBundle\Router\ClaroRouter;
use Claroline\LdapBundle\Manager\LdapManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bridge\Twig\TwigEngine;

/**
 * @DI\Service()
 */
class ExternalAuthenticationListener
{
    /** @var TwigEngine */
    private $templating;
    /** @var LdapManager */
    private $ldapManager;
    /** @var ClaroRouter */
    private $router;

    /**
     * @DI\InjectParams({
     *     "templating"             = @DI\Inject("templating"),
     *     "ldapManager"            = @DI\Inject("claroline.ldap_bundle.manager.ldap_manager"),
     *     "router"                 = @DI\Inject("router")
     * })
     *
     * @param TwigEngine  $templating
     * @param LdapManager $ldapManager
     * @param ClaroRouter $router
     */
    public function __construct(
        TwigEngine $templating,
        LdapManager $ldapManager,
        ClaroRouter $router
    ) {
        $this->templating = $templating;
        $this->ldapManager = $ldapManager;
        $this->router = $router;
    }

    /**
     * @DI\Observe("render_external_authentication_button", priority=2)
     *
     * @param RenderAuthenticationButtonEvent $event
     *
     * @return string
     */
    public function onRenderButton(RenderAuthenticationButtonEvent $event)
    {
        $servers = $this->ldapManager->getActiveServers();
        if (count($servers) > 0) {
            $content = $this->templating->render(
                'ClarolineLdapBundle:Ldap:buttons.html.twig',
                ['servers' => $servers]
            );
            $event->addContent($content);
        }
    }

    /**
     * @DI\Observe("external_login_target_url_event", priority=2)
     *
     * @param LoginTargetUrlEvent $event
     */
    public function onExternalLoginTargetUrl(LoginTargetUrlEvent $event)
    {
        $servers = $this->ldapManager->getActiveServers();
        if (count($servers) > 0) {
            foreach ($servers as $server) {
                $event->addTarget(
                    $server,
                    $this->router->generate('claro_ldap_login', ['name' => $server])
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
                $this->ldapManager->unlinkAccount($receiver->getId());
            }
        }
    }
}

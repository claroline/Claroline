<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/*
 * Copied from the Sf2 AnonymousAuthenticationListener
 */
class AnonymousAuthenticationListener implements ListenerInterface
{
    private $context;
    private $key;
    private $logger;

    public function __construct(SecurityContextInterface $context, $key, LoggerInterface $logger = null)
    {
        $this->context = $context;
        $this->key     = $key;
        $this->logger  = $logger;
    }

    public function handle(GetResponseEvent $event)
    {
        if (null !== $this->context->getToken()) {
            return;
        }

        $this->context->setToken(new AnonymousToken($this->key, 'anon.', array('ROLE_ANONYMOUS')));

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Populated SecurityContext with an anonymous Token'));
        }
    }
}
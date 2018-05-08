<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;

/**
 * AnonymousAuthenticationListener automatically adds a Token if none is already present.
 *
 * NB. This listener is not directly defined as a service as it only serves as a
 * replacement class for the Symfony original one (see app/config.yml).
 */
class AnonymousAuthenticationListener implements ListenerInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var string */
    private $secret;

    /** @var LoggerInterface */
    private $logger;

    /**
     * AnonymousAuthenticationListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     * @param string                $secret
     * @param LoggerInterface       $logger
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        $secret,
        LoggerInterface $logger = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->secret = $secret;
        $this->logger = $logger;
    }

    /**
     * Authenticates anonymous with correct roles.
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        if (null !== $this->tokenStorage->getToken()) {
            // user is already authenticated, there is nothing to do.
            return;
        }

        // creates an anonymous token with a dedicated role.
        $this->tokenStorage->setToken(
            new AnonymousToken($this->secret, 'anon.', ['ROLE_ANONYMOUS'])
        );

        if (null !== $this->logger) {
            $this->logger->info(sprintf('Populated SecurityContext with an anonymous Token'));
        }
    }
}

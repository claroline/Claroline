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

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/*
 * Populates the anonymous token with a dedicated role.
 *
 * This listener is not directly defined as a service as it only serves as a
 * replacement class for the Symfony original one (see app/config.yml).
 */
class AnonymousAuthenticationListener implements ListenerInterface
{
    private $context;
    private $key;
    private $logger;

    public function __construct(TokenStorageInterface $context, $key, LoggerInterface $logger = null)
    {
        $this->context = $context;
        $this->key = $key;
        $this->logger = $logger;
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

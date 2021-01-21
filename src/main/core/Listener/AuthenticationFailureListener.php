<?php

namespace Claroline\CoreBundle\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class AuthenticationFailureListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onAuthenticationFailure(AuthenticationFailureEvent $authenticationFailureEvent)
    {
        $exception = $authenticationFailureEvent->getAuthenticationException();

        $this->logger->error($exception->getMessage(), ['exception' => $exception]);
    }
}

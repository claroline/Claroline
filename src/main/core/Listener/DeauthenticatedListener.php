<?php

namespace Claroline\CoreBundle\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Http\Event\DeauthenticatedEvent;

class DeauthenticatedListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onDeauthentication(DeauthenticatedEvent $deauthenticatedEvent)
    {
        $originalUser = $deauthenticatedEvent->getOriginalToken()->getUsername();
        $refreshedUser = $deauthenticatedEvent->getRefreshedToken()->getUsername();

        $this->logger->error(sprintf("Deauthentication on %s with %s", $originalUser, $refreshedUser));
    }
}
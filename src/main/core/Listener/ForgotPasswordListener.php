<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogForgotPasswordEvent;
use Psr\Log\LoggerInterface;

class ForgotPasswordListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onForgoPassword(LogForgotPasswordEvent $logForgotPasswordEvent)
    {
        //todo: do something
    }
}
<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Event\Log\LogNewPasswordEvent;
use Psr\Log\LoggerInterface;

class NewPasswordListener
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onNewPassword(LogNewPasswordEvent $logNewPasswordEvent)
    {
        //todo: do something
    }
}
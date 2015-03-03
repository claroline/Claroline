<?php

namespace Claroline\InstallationBundle\Log;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

Trait LoggableTrait
{
    use LoggerAwareTrait;

    private function log($message)
    {
        $this->logger->log(LogLevel::INFO, $message);
    }
}

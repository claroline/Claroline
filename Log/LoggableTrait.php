<?php

namespace Claroline\InstallationBundle\Log;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

Trait LoggableTrait
{
    use LoggerAwareTrait;

    public function log($message)
    {
        if (null !== $this->logger) {
            $this->logger->log(LogLevel::INFO, $message);
        }
    }
}

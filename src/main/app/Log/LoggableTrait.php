<?php

namespace Claroline\AppBundle\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @deprecated use Psr\Log\LoggerAwareTrait
 */
trait LoggableTrait
{
    /** @var LoggerInterface */
    protected $logger;

    public function log($message, $logLevel = null): void
    {
        if ($this->logger) {
            $time = date('m-d-y h:i:s').': ';
            if (!$logLevel) {
                $logLevel = LogLevel::INFO;
            }
            $this->logger->log($logLevel, $time.$message);
        }
    }

    /**
     * Sets a logger.
     */
    public function setLogger(LoggerInterface $logger = null): void
    {
        $this->logger = $logger;
    }
}

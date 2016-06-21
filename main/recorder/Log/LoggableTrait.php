<?php

namespace Claroline\BundleRecorder\Log;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

trait LoggableTrait
{
    use LoggerAwareTrait;

    public function log($message, $logLevel = null)
    {
        if ($this->logger) {
            $time = date('m-d-y h:i:s').': ';
            if (!$logLevel) {
                $logLevel = LogLevel::INFO;
            }
            $this->logger->log($logLevel, $time.$message);
        }
    }
}

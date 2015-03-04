<?php

namespace Claroline\InstallationBundle\Updater;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

abstract class Updater
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param $message
     */
    public function log($message)
    {
        $this->logger->log(LogLevel::INFO, $message);
    }
}

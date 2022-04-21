<?php

namespace Claroline\LogBundle\Archive;

use Claroline\AppBundle\Persistence\ObjectManager;

class LogRotator implements LogRotatorInterface
{
    public function __construct(ObjectManager $om)
    {
        
    }

    public function rotateSecurityLogs(\DateTime $from, \DateTime $to): void
    {

    }

    public function rotateFunctionalLogs(\DateTime $from, \DateTime $to): void
    {

    }

    public function rotateMessageLogs(\DateTime $from, \DateTime $to): void
    {

    }
}
<?php

namespace Claroline\LogBundle\Archive;

interface LogRotatorInterface
{
    public function rotateSecurityLogs(\DateTime $from, \DateTime $to): void;
    public function rotateFunctionalLogs(\DateTime $from, \DateTime $to): void;
    public function rotateMessageLogs(\DateTime $from, \DateTime to): void;
}
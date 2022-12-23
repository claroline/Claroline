<?php

namespace Claroline\LogBundle\Archive;

interface LogRotatorInterface
{
    public function rotateLogs(\DateTimeInterface $interval, string $type): void;
}
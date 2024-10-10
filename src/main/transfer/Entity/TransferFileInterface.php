<?php

namespace Claroline\TransferBundle\Entity;

interface TransferFileInterface
{
    public const PENDING = 'pending';
    public const IN_PROGRESS = 'in_progress';
    public const SUCCESS = 'success';
    public const ERROR = 'error';

    public function getStatus(): string;

    public function getAction(): ?string;

    // for retro compatibility with old import logs. Replace by getUuid() when I'll rework logs format.
    // (we will loose old logs)
    public function getLog(): string;
}

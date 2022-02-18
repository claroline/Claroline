<?php

namespace Claroline\TransferBundle\Entity;

interface TransferFileInterface
{
    const PENDING = 'pending';
    const IN_PROGRESS = 'in_progress';
    const SUCCESS = 'success';
    const ERROR = 'error';

    public function getStatus(): string;

    public function getAction(): ?string;

    // for retro compatibility with old import logs. Replace by getUuid() when I'll reworks logs format.
    // (we will loose old logs)
    public function getLog(): string;
}

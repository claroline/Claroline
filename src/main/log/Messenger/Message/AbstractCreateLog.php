<?php

namespace Claroline\LogBundle\Messenger\Message;

abstract class AbstractCreateLog
{
    public function __construct(
        private readonly \DateTimeInterface $date,
        private readonly string $action,
        private readonly string $details,
        private readonly ?int $doerId = null
    ) {
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getDetails(): string
    {
        return $this->details;
    }

    public function getDoerId(): ?int
    {
        return $this->doerId;
    }
}

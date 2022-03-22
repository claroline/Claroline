<?php

namespace Claroline\LogBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class CreateSecurityLog implements AsyncMessageInterface
{
    /** @var \DateTimeInterface */
    private $date;
    /** @var string */
    private $action;
    /** @var string */
    private $details;
    /** @var string */
    private $doerIp;
    /** @var int */
    private $doerId;
    /** @var int */
    private $targetId;

    public function __construct(
        \DateTimeInterface $date,
        string $action,
        string $details,
        string $doerIp,
        ?int $doerId = null,
        ?int $targetId = null
    ) {
        $this->date = $date;
        $this->action = $action;
        $this->details = $details;
        $this->doerIp = $doerIp;
        $this->doerId = $doerId;
        $this->targetId = $targetId;
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

    public function getDoerIp(): string
    {
        return $this->doerIp;
    }

    public function getDoerId(): ?int
    {
        return $this->doerId;
    }

    public function getTargetId(): ?int
    {
        return $this->targetId;
    }
}

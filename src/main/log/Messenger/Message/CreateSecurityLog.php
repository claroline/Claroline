<?php

namespace Claroline\LogBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;
use Claroline\CoreBundle\Entity\User;

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
    /** @var User */
    private $doer;
    /** @var User */
    private $target;

    public function __construct(
        \DateTimeInterface $date,
        string $action,
        string $details,
        string $doerIp,
        ?User $doer = null,
        ?User $target = null
    ) {
        $this->date = $date;
        $this->action = $action;
        $this->details = $details;
        $this->doerIp = $doerIp;
        $this->doer = $doer;
        $this->target = $target;
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

    public function getDoer(): ?User
    {
        return $this->doer;
    }

    public function getTarget(): ?User
    {
        return $this->target;
    }
}

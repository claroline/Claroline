<?php

namespace Claroline\LogBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;
use Claroline\CoreBundle\Entity\User;

class CreateSecurityLogs implements AsyncMessageInterface
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
    /** @var array */
    private $targets;
    /** @var string|null */
    private $doerCountry;
    /** @var string|null */
    private $doerCity;

    public function __construct(
        \DateTimeInterface $date,
        string $action,
        string $details,
        string $doerIp,
        ?User $doer = null,
        array $targets = [],
        ?string $doerCountry = null,
        ?string $doerCity = null
    ) {
        $this->date = $date;
        $this->action = $action;
        $this->details = $details;
        $this->doerIp = $doerIp;
        $this->doer = $doer;
        $this->targets = $targets;
        $this->doerCountry = $doerCountry;
        $this->doerCity = $doerCity;
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

    public function getTargets(): array
    {
        return $this->targets;
    }

    public function getDoerCountry(): ?string
    {
        return $this->doerCountry;
    }

    public function getDoerCity(): ?string
    {
        return $this->doerCity;
    }
}

<?php

namespace Claroline\LogBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;

class CreateRoleChangeLogs implements AsyncMessageInterface
{
    /** @var \DateTimeInterface */
    private $date;
    /** @var string */
    private $action;
    /** @var Role */
    private $role;
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
        Role $role,
        string $doerIp,
        ?User $doer = null,
        array $targets = [],
        ?string $doerCountry = null,
        ?string $doerCity = null
    ) {
        $this->date = $date;
        $this->action = $action;
        $this->role = $role;
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

    public function getRole(): Role
    {
        return $this->role;
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

<?php

namespace Claroline\LogBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class CreateRoleChangeLogs implements AsyncMessageInterface
{
    /** @var \DateTimeInterface */
    private $date;
    /** @var string */
    private $action;
    /** @var int */
    private $roleId;
    /** @var string */
    private $doerIp;
    /** @var int */
    private $doerId;
    /** @var array */
    private $targetIds;

    public function __construct(
        \DateTimeInterface $date,
        string $action,
        int $roleId,
        string $doerIp,
        ?int $doerId = null,
        array $targetIds = []
    ) {
        $this->date = $date;
        $this->action = $action;
        $this->roleId = $roleId;
        $this->doerIp = $doerIp;
        $this->doerId = $doerId;
        $this->targetIds = $targetIds;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getRoleId(): int
    {
        return $this->roleId;
    }

    public function getDoerIp(): string
    {
        return $this->doerIp;
    }

    public function getDoerId(): ?int
    {
        return $this->doerId;
    }

    public function getTargetIds(): array
    {
        return $this->targetIds;
    }
}

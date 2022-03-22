<?php

namespace Claroline\LogBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class CreateFunctionalLog implements AsyncMessageInterface
{
    /** @var \DateTimeInterface */
    private $date;
    /** @var string */
    private $action;
    /** @var string */
    private $details;
    /** @var int */
    private $doerId;
    /** @var int */
    private $workspaceId;
    /** @var int */
    private $resourceNodeId;

    public function __construct(
        \DateTimeInterface $date,
        string $action,
        string $details,
        int $doerId,
        ?int $workspaceId = null,
        ?int $resourceNodeId = null
    ) {
        $this->date = $date;
        $this->action = $action;
        $this->details = $details;
        $this->doerId = $doerId;
        $this->workspaceId = $workspaceId;
        $this->resourceNodeId = $resourceNodeId;
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

    public function getDoerId(): int
    {
        return $this->doerId;
    }

    public function getWorkspaceId(): ?int
    {
        return $this->workspaceId;
    }

    public function getResourceNodeId(): ?int
    {
        return $this->resourceNodeId;
    }
}

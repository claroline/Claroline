<?php

namespace Claroline\LogBundle\Messenger\Message;

/**
 * Create FunctionalLog message.
 * This message is not directly handled by the messenger,
 * it is used as a sub message for SubmitLogs to know which logs needs to be created.
 */
class CreateFunctionalLog extends AbstractCreateLog
{
    public function __construct(
        \DateTimeInterface $date,
        string $action,
        string $details,
        int $doerId = null,
        private readonly ?int $workspaceId = null,
        private readonly ?int $resourceNodeId = null
    ) {
        parent::__construct($date, $action, $details, $doerId);
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

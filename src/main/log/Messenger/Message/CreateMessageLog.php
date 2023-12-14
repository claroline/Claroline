<?php

namespace Claroline\LogBundle\Messenger\Message;

/**
 * Create MessageLog message.
 * This message is not directly handled by the messenger,
 * it is used as a sub message for SubmitLogs to know which logs needs to be created.
 */
class CreateMessageLog extends AbstractCreateLog
{
    public function __construct(
        \DateTimeInterface $date,
        string $action,
        string $details,
        int $doerId = null,
        private readonly ?int $receiverId = null
    ) {
        parent::__construct($date, $action, $details, $doerId);
    }

    public function getReceiverId(): ?int
    {
        return $this->receiverId;
    }
}

<?php

namespace Claroline\LogBundle\Messenger\Message;

/**
 * Create OperationalLog message.
 * This message is not directly handled by the messenger,
 * it is used as a sub message for SubmitLogs to know which logs needs to be created.
 */
class CreateOperationalLog extends AbstractCreateLog
{
    public function __construct(
        \DateTimeInterface $date,
        string $action,
        string $details,
        int $doerId = null,
        private readonly string $objectClass,
        private readonly string $objectId,
        private readonly ?array $changeset = []
    ) {
        parent::__construct($date, $action, $details, $doerId);
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    public function getObjectId(): string
    {
        return $this->objectId;
    }

    public function getChangeset(): array
    {
        return $this->changeset;
    }
}

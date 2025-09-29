<?php

namespace Claroline\LogBundle\Messenger\Message;

/**
 * Create FunctionalLog message.
 * This message is not directly handled by the messenger.
 * It is used as a sub message for SubmitLogs to know which logs needs to be created.
 */
class CreateFunctionalLog extends AbstractCreateLog
{
    public function __construct(
        \DateTimeInterface $date,
        string $action,
        string $details,
        int $doerId = null,
        private readonly string $objectClass,
        private readonly string $objectId,
        private readonly ?string $contextId = null
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

    public function getContextId(): ?string
    {
        return $this->contextId;
    }
}

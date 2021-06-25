<?php

namespace Claroline\LogBundle\Messenger\Security\Message;

class AddRoleMessage implements SecurityMessageInterface
{
    public const EVENT_NAME = 'event.security.add_role';

    private $targetId;
    private $doerId;
    private $message;

    public function __construct(
        int $targetId,
        int $doerId,
        string $message
    ) {
        $this->targetId = $targetId;
        $this->doerId = $doerId;
        $this->message = $message;
    }

    public function getTargetId(): int
    {
        return $this->targetId;
    }

    public function getDoerId(): int
    {
        return $this->doerId;
    }

    public function getEventName(): string
    {
        return self::EVENT_NAME;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}

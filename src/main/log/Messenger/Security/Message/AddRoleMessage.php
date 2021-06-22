<?php

namespace Claroline\LogBundle\Messenger\Security\Message;

class AddRoleMessage implements SecurityMessageInterface
{
    private $targetId;
    private $doerId;
    private $eventName;
    private $message;

    public function __construct(
        int $targetId,
        int $doerId,
        string $eventName,
        string $message
    ) {
        $this->targetId = $targetId;
        $this->doerId = $doerId;
        $this->eventName = $eventName;
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

    public function getName(): string
    {
        return $this->eventName;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}

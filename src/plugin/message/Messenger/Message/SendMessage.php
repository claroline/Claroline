<?php

namespace Claroline\MessageBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class SendMessage implements AsyncMessageInterface
{
    private $message;
    private $eventName;
    private $receiverId;
    private $senderId;

    public function __construct(
        string $message,
        string $eventName,
        int $receiverId,
        int $senderId
    ) {
        $this->message = $message;
        $this->eventName = $eventName;
        $this->receiverId = $receiverId;
        $this->senderId = $senderId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getReceiverId(): int
    {
        return $this->receiverId;
    }

    public function getSenderId(): int
    {
        return $this->senderId;
    }
}

<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\ForumBundle\Entity\Message;

class ForumNotification
{
    /** @var Message */
    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}

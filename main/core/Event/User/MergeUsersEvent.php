<?php

namespace Claroline\CoreBundle\Event\User;

use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when two users are merged.
 */
class MergeUsersEvent extends Event
{
    /** @var User */
    private $kept;

    /** @var User */
    private $removed;

    /** @var array */
    private $messages = [];

    /**
     * MergeUsersEvent constructor.
     *
     * @param User  $kept
     * @param User  $removed
     * @param array $messages
     */
    public function __construct(
        User $kept,
        User $removed,
        array $messages = [])
    {
        $this->kept = $kept;
        $this->removed = $removed;
        $this->messages = $messages;
    }

    /**
     * Gets the user whose account will be kept.
     *
     * @return User
     */
    public function getKept()
    {
        return $this->kept;
    }

    /**
     * Gets the user whose account will be removed.
     *
     * @return User
     */
    public function getRemoved()
    {
        return $this->removed;
    }

    /**
     * Get cutom messages added to the event.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Adds custom message to the event.
     *
     * @param string $message
     */
    public function addMessage($message)
    {
        $this->messages[] = $message;
    }
}

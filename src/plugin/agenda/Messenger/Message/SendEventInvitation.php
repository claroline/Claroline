<?php

namespace Claroline\AgendaBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

/**
 * Send an invitation to an event to a user.
 */
class SendEventInvitation implements AsyncMessageInterface
{
    /**
     * The auto id of the EventInvitation to be sent.
     *
     * @var int
     */
    private $invitationId;

    public function __construct(int $invitationId)
    {
        $this->invitationId = $invitationId;
    }

    public function getInvitationId(): int
    {
        return $this->invitationId;
    }
}

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

    /**
     * The path to the exported ICS of the event which will be sent as attachment.
     *
     * @var string
     */
    private $icsPath;

    public function __construct(int $invitationId, string $icsPath)
    {
        $this->invitationId = $invitationId;
        $this->icsPath = $icsPath;
    }

    public function getInvitationId(): int
    {
        return $this->invitationId;
    }

    public function getICSPath(): string
    {
        return $this->icsPath;
    }
}

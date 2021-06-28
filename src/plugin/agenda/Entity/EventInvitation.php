<?php

namespace Claroline\AgendaBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * EventUsers.
 *
 * @ORM\Table(name="claro_event_invitation")
 * @ORM\Entity
 */
class EventInvitation
{
    use Id;

    const UNKNOWN = 'unknown';
    const JOIN = 'join';
    const MAYBE = 'maybe';
    const RESIGN = 'resign';

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $status = self::UNKNOWN;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\AgendaBundle\Entity\Event", inversedBy="eventInvitations")
     * @ORM\JoinColumn(name="event", nullable=false, onDelete="CASCADE")
     *
     * @var Event
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    public function __construct(Event $event, User $user)
    {
        $this->event = $event;
        $this->user = $user;
    }

    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}

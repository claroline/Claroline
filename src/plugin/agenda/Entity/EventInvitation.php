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

    const IGNORE = 0;
    const JOIN = 1;
    const MAYBE = 2;
    const RESIGN = 3;

    /**
     * @ORM\Column(type="smallint")
     *
     * @var int
     */
    private $status = self::IGNORE;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\AgendaBundle\Entity\Event", inversedBy="eventInvitations")
     * @ORM\JoinColumn(name="event", nullable=false, onDelete="cascade")
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    public function __construct(Event $event, User $user)
    {
        $this->event = $event;
        $this->user = $user;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return EventInvitation
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return EventInvitation
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set event.
     *
     * @return EventInvitation
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set user.
     *
     * @return EventInvitation
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return EventInvitation
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}

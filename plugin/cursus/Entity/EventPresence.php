<?php

namespace Claroline\CursusBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_cursusbundle_presence_status")
 * @ORM\Entity
 */
class EventPresence
{
    use Id;
    use Uuid;

    const UNKNOWN = 'unknown';
    const PRESENT = 'present';
    const ABSENT_JUSTIFIED = 'absent_justified';
    const ABSENT_UNJUSTIFIED = 'absent_unjustified';

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Event")
     * @ORM\JoinColumn(name="event_id", nullable=false, onDelete="CASCADE")
     *
     * @var Event
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\Column(name="presence_status", nullable=false)
     *
     * @var string
     */
    private $status = self::UNKNOWN;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}

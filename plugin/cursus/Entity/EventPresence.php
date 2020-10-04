<?php

namespace Claroline\CursusBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CursusBundle\Entity\Registration\EventUser;
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
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Registration\EventUser")
     * @ORM\JoinColumn(name="event_user_id", nullable=false, onDelete="CASCADE")
     *
     * @var EventUser
     */
    private $eventUser;

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

    public function getEventUser(): EventUser
    {
        return $this->eventUser;
    }

    public function setEventUser(EventUser $eventUser)
    {
        $this->eventUser = $eventUser;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }
}

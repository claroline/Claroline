<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\SessionEventUserRepository")
 * @ORM\Table(name="claro_cursusbundle_session_event_user")
 */
class SessionEventUser
{
    use UuidTrait;

    const REGISTERED = 0;
    const PENDING = 1;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\SessionEvent",
     *     inversedBy="sessionEventUsers"
     * )
     * @ORM\JoinColumn(name="session_event_id", nullable=false, onDelete="CASCADE")
     */
    protected $sessionEvent;

    /**
     * @ORM\Column(name="registration_status", type="integer", nullable=false)
     */
    protected $registrationStatus = self::REGISTERED;

    /**
     * @ORM\Column(name="registration_date", type="datetime", nullable=true)
     */
    protected $registrationDate;

    /**
     * @ORM\Column(name="application_date", type="datetime", nullable=true)
     */
    protected $applicationDate;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\PresenceStatus")
     * @ORM\JoinColumn(name="presence_status_id", nullable=true, onDelete="SET NULL")
     */
    protected $presenceStatus;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getSessionEvent()
    {
        return $this->sessionEvent;
    }

    public function setSessionEvent(SessionEvent $sessionEvent)
    {
        $this->sessionEvent = $sessionEvent;
    }

    public function getRegistrationStatus()
    {
        return $this->registrationStatus;
    }

    public function setRegistrationStatus($registrationStatus)
    {
        $this->registrationStatus = $registrationStatus;
    }

    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;
    }

    public function getApplicationDate()
    {
        return $this->applicationDate;
    }

    public function setApplicationDate($applicationDate)
    {
        $this->applicationDate = $applicationDate;
    }

    public function getPresenceStatus()
    {
        return $this->presenceStatus;
    }

    public function setPresenceStatus(PresenceStatus $presenceStatus = null)
    {
        $this->presenceStatus = $presenceStatus;
    }
}

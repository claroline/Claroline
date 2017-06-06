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

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\SessionEventUserRepository")
 * @ORM\Table(name="claro_cursusbundle_session_event_user")
 */
class SessionEventUser
{
    const REGISTERED = 0;
    const PENDING = 1;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_user_min"})
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\SessionEvent",
     *     inversedBy="sessionEventUsers"
     * )
     * @ORM\JoinColumn(name="session_event_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_cursus", "api_user_min"})
     * @SerializedName("sessionEvent")
     */
    protected $sessionEvent;

    /**
     * @ORM\Column(name="registration_status", type="integer", nullable=false)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min"})
     * @SerializedName("registrationStatus")
     */
    protected $registrationStatus = self::REGISTERED;

    /**
     * @ORM\Column(name="registration_date", type="datetime", nullable=true)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min"})
     * @SerializedName("registrationDate")
     */
    protected $registrationDate;

    /**
     * @ORM\Column(name="application_date", type="datetime", nullable=true)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min"})
     * @SerializedName("applicationDate")
     */
    protected $applicationDate;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\PresenceStatus")
     * @ORM\JoinColumn(name="presence_status_id", nullable=true, onDelete="SET NULL")
     * @Groups({"api_user_min"})
     * @SerializedName("presenceStatus")
     */
    protected $presenceStatus;

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

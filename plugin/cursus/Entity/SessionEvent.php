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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\SessionEventRepository")
 * @ORM\Table(name="claro_cursusbundle_session_event")
 */
class SessionEvent
{
    use Id;
    use Uuid;

    const TYPE_NONE = 0;
    const TYPE_EVENT = 1;

    /**
     * @ORM\Column(name="event_name")
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $code;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession",
     *     inversedBy="events",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     *
     * @var CourseSession
     */
    protected $session;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    protected $endDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Location")
     * @ORM\JoinColumn(name="location_id", nullable=true, onDelete="SET NULL")
     *
     * @var Location
     */
    protected $location;

    /**
     * @ORM\Column(name="location_extra", type="text", nullable=true)
     */
    protected $locationExtra;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="claro_cursusbundle_session_event_tutors")
     *
     * @var User[]|ArrayCollection
     */
    protected $tutors;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\SessionEventUser",
     *     mappedBy="sessionEvent"
     * )
     */
    protected $sessionEventUsers;

    /**
     * @ORM\Column(name="max_users", nullable=true, type="integer")
     */
    protected $maxUsers;

    /**
     * @ORM\Column(name="registration_type", type="integer", nullable=false, options={"default" = 0})
     */
    protected $registrationType = CourseSession::REGISTRATION_AUTO;

    /**
     * @ORM\Column(name="event_type", type="integer", nullable=false, options={"default" = 0})
     */
    protected $type = self::TYPE_NONE;

    public function __construct()
    {
        $this->refreshUuid();

        $this->sessionEventUsers = new ArrayCollection();
        $this->tutors = new ArrayCollection();
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getSession()
    {
        return $this->session;
    }

    public function setSession(CourseSession $session)
    {
        $this->session = $session;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    public function isActive()
    {
        $now = new \DateTime();

        return $now >= $this->startDate && $now <= $this->endDate;
    }

    public function hasStarted()
    {
        $now = new \DateTime();

        return is_null($this->startDate) || $now >= $this->startDate;
    }

    public function isTerminated()
    {
        $now = new \DateTime();

        return $this->endDate && $now > $this->endDate;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation(Location $location = null)
    {
        $this->location = $location;
    }

    public function getLocationExtra()
    {
        return $this->locationExtra;
    }

    public function setLocationExtra($locationExtra)
    {
        $this->locationExtra = $locationExtra;
    }

    /**
     * @return User[]
     */
    public function getTutors()
    {
        return $this->tutors->toArray();
    }

    public function addTutor(User $tutor)
    {
        if (!$this->tutors->contains($tutor)) {
            $this->tutors->add($tutor);
        }

        return $this;
    }

    public function removeTutor(User $tutor)
    {
        if ($this->tutors->contains($tutor)) {
            $this->tutors->removeElement($tutor);
        }

        return $this;
    }

    public function emptyTutors()
    {
        $this->tutors->clear();
    }

    public function getSessionEventUsers()
    {
        return $this->sessionEventUsers->toArray();
    }

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    public function getRegistrationType()
    {
        return $this->registrationType;
    }

    public function setRegistrationType($registrationType)
    {
        $this->registrationType = $registrationType;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}

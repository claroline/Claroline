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
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FormaLibre\ReservationBundle\Entity\Reservation;
use FormaLibre\ReservationBundle\Entity\Resource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\SessionEventRepository")
 * @ORM\Table(name="claro_cursusbundle_session_event")
 */
class SessionEvent
{
    use UuidTrait;

    const TYPE_NONE = 0;
    const TYPE_EVENT = 1;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="event_name")
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession",
     *     inversedBy="events",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="session_id", nullable=false, onDelete="CASCADE")
     */
    protected $session;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=false)
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=false)
     */
    protected $endDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Location")
     * @ORM\JoinColumn(name="location_id", nullable=true, onDelete="SET NULL")
     */
    protected $location;

    /**
     * @ORM\Column(name="location_extra", type="text", nullable=true)
     */
    protected $locationExtra;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\SessionEventComment",
     *     mappedBy="sessionEvent"
     * )
     */
    protected $comments;

    /**
     * @ORM\ManyToOne(targetEntity="FormaLibre\ReservationBundle\Entity\Resource")
     * @ORM\JoinColumn(name="location_resource_id", nullable=true, onDelete="SET NULL")
     */
    protected $locationResource;

    /**
     * @ORM\ManyToOne(targetEntity="FormaLibre\ReservationBundle\Entity\Reservation")
     * @ORM\JoinColumn(name="reservation_id", nullable=true, onDelete="SET NULL")
     */
    protected $reservation;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="claro_cursusbundle_session_event_tutors")
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

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\SessionEventSet",
     *     inversedBy="events"
     * )
     * @ORM\JoinColumn(name="event_set", nullable=true, onDelete="SET NULL")
     */
    protected $eventSet;

    public function __construct()
    {
        $this->refreshUuid();
        $this->comments = new ArrayCollection();
        $this->sessionEventUsers = new ArrayCollection();
        $this->tutors = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
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

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

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

    public function getComments()
    {
        return $this->comments->toArray();
    }

    public function getLocationResource()
    {
        return $this->locationResource;
    }

    public function setLocationResource(Resource $locationResource = null)
    {
        $this->locationResource = $locationResource;
    }

    public function getReservation()
    {
        return $this->reservation;
    }

    public function setReservation(Reservation $reservation = null)
    {
        $this->reservation = $reservation;
    }

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

    public function getEventSet()
    {
        return $this->eventSet;
    }

    public function setEventSet(SessionEventSet $eventSet = null)
    {
        $this->eventSet = $eventSet;
    }

    public static function getSearchableFields()
    {
        return ['name'];
    }
}

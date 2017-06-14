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

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseSessionRepository")
 * @ORM\Table(name="claro_cursusbundle_course_session")
 */
class CourseSession
{
    const SESSION_NOT_STARTED = 0;
    const SESSION_OPEN = 1;
    const SESSION_CLOSED = 2;
    const REGISTRATION_AUTO = 0;
    const REGISTRATION_MANUAL = 1;
    const REGISTRATION_PUBLIC = 2;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_cursus", "api_cursus_min", "api_bulletin", "api_user_min", "api_group_min", "api_workspace_min"})
     */
    protected $id;

    /**
     * @ORM\Column(name="session_name")
     * @Assert\NotBlank()
     * @Groups({"api_cursus", "api_cursus_min", "api_bulletin", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("name")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Course",
     *     inversedBy="sessions"
     * )
     * @ORM\JoinColumn(name="course_id", nullable=false, onDelete="CASCADE")
     * @Groups({"api_cursus", "api_cursus_min", "api_bulletin", "api_user_min", "api_group_min", "api_workspace_min"})
     */
    protected $course;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("description")
     */
    protected $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="SET NULL")
     * @Groups({"api_workspace_min", "api_user_min"})
     */
    protected $workspace;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     * @ORM\JoinColumn(name="learner_role_id", nullable=true, onDelete="SET NULL")
     */
    protected $learnerRole;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     * @ORM\JoinColumn(name="tutor_role_id", nullable=true, onDelete="SET NULL")
     */
    protected $tutorRole;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\Cursus"
     * )
     * @ORM\JoinTable(name="claro_cursus_sessions")
     * @Groups({"api_user_min"})
     */
    protected $cursus;

    /**
     * @ORM\Column(name="session_status", type="integer")
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("sessionStatus")
     */
    protected $sessionStatus = self::SESSION_NOT_STARTED;

    /**
     * @ORM\Column(name="default_session", type="boolean")
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("defaultSession")
     */
    protected $defaultSession = false;

    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("creationDate")
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="public_registration", type="boolean")
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("publicRegistration")
     */
    protected $publicRegistration = false;

    /**
     * @ORM\Column(name="public_unregistration", type="boolean")
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("publicUnregistration")
     */
    protected $publicUnregistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("registrationValidation")
     */
    protected $registrationValidation = false;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("startDate")
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("endDate")
     */
    protected $endDate;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSessionUser",
     *     mappedBy="session"
     * )
     */
    protected $sessionUsers;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSessionGroup",
     *     mappedBy="session"
     * )
     */
    protected $sessionGroups;

    /**
     * @Groups({"api_bulletin"})
     * @SerializedName("extra")
     */
    protected $extra = [];

    /**
     * @ORM\Column(name="user_validation", type="boolean")
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("userValidation")
     */
    protected $userValidation = false;

    /**
     * @ORM\Column(name="organization_validation", type="boolean")
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("organizationValidation")
     */
    protected $organizationValidation = false;

    /**
     * @ORM\Column(name="max_users", nullable=true, type="integer")
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("maxUsers")
     */
    protected $maxUsers;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinTable(name="claro_cursusbundle_course_session_validators")
     * @Groups({"api_user_min"})
     */
    protected $validators;

    /**
     * @ORM\Column(name="session_type", type="integer")
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     */
    protected $type = 0;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\SessionEvent",
     *     mappedBy="session"
     * )
     * @ORM\OrderBy({"startDate" = "ASC"})
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $events;

    /**
     * @ORM\Column(name="event_registration_type", type="integer", nullable=false, options={"default" = 0})
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_workspace_min"})
     * @SerializedName("eventRegistrationType")
     */
    protected $eventRegistrationType = self::REGISTRATION_AUTO;

    /**
     * @ORM\Column(name="display_order", type="integer", options={"default" = 500})
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_bulletin", "api_workspace_min"})
     * @SerializedName("displayOrder")
     */
    protected $displayOrder = 500;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_bulletin", "api_workspace_min"})
     */
    protected $details;

    public function __construct()
    {
        $this->cursus = new ArrayCollection();
        $this->sessionUsers = new ArrayCollection();
        $this->sessionGroups = new ArrayCollection();
        $this->validators = new ArrayCollection();
        $this->events = new ArrayCollection();
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

    public function getCourse()
    {
        return $this->course;
    }

    public function setCourse(Course $course)
    {
        $this->course = $course;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    public function getCursus()
    {
        return $this->cursus->toArray();
    }

    public function addCursu(Cursus $cursus)
    {
        if (!$this->cursus->contains($cursus)) {
            $this->cursus->add($cursus);
        }

        return $this;
    }

    public function addCursus(Cursus $cursus)
    {
        if (!$this->cursus->contains($cursus)) {
            $this->cursus->add($cursus);
        }

        return $this;
    }

    public function removeCursu(Cursus $cursus)
    {
        if ($this->cursus->contains($cursus)) {
            $this->cursus->removeElement($cursus);
        }

        return $this;
    }

    public function emptyCursus()
    {
        $this->cursus->clear();
    }

    public function getSessionStatus()
    {
        return $this->sessionStatus;
    }

    public function setSessionStatus($sessionStatus)
    {
        $this->sessionStatus = $sessionStatus;
    }

    public function getLearnerRole()
    {
        return $this->learnerRole;
    }

    public function setLearnerRole(Role $learnerRole = null)
    {
        $this->learnerRole = $learnerRole;
    }

    public function getTutorRole()
    {
        return $this->tutorRole;
    }

    public function setTutorRole(Role $tutorRole = null)
    {
        $this->tutorRole = $tutorRole;
    }

    public function isDefaultSession()
    {
        return $this->defaultSession;
    }

    public function setDefaultSession($defaultSession)
    {
        $this->defaultSession = $defaultSession;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getPublicRegistration()
    {
        return $this->publicRegistration;
    }

    public function setPublicRegistration($publicRegistration)
    {
        $this->publicRegistration = $publicRegistration;
    }

    public function getPublicUnregistration()
    {
        return $this->publicUnregistration;
    }

    public function setPublicUnregistration($publicUnregistration)
    {
        $this->publicUnregistration = $publicUnregistration;
    }

    public function getRegistrationValidation()
    {
        return $this->registrationValidation;
    }

    public function setRegistrationValidation($registrationValidation)
    {
        $this->registrationValidation = $registrationValidation;
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

    public function getSessionUsers()
    {
        return $this->sessionUsers->toArray();
    }

    public function getSessionGroups()
    {
        return $this->sessionGroups->toArray();
    }

    public function getCourseTitle()
    {
        return $this->getCourse()->getTitle();
    }

    public function getFullNameWithCourse()
    {
        return $this->getCourseTitle().
            ' ['.
            $this->getCourse()->getCode().
            ']'.
            ' - '.
            $this->getName();
    }

    public function getShortNameWithCourse($courseLength = 25)
    {
        $courseTitle = $this->getCourseTitle();
        $length = strlen($courseTitle);
        $shortTitle = ($length > $courseLength) ?
            substr($courseTitle, 0, $courseLength).'...' :
            $courseTitle;

        return $shortTitle.' - '.$this->getName();
    }

    public function setExtra(array $extra)
    {
        $this->extra = $extra;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function getUserValidation()
    {
        return $this->userValidation;
    }

    public function setUserValidation($userValidation)
    {
        $this->userValidation = $userValidation;
    }

    public function getOrganizationValidation()
    {
        return $this->organizationValidation;
    }

    public function setOrganizationValidation($organizationValidation)
    {
        $this->organizationValidation = $organizationValidation;
    }

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    public function getValidators()
    {
        return $this->validators->toArray();
    }

    public function addValidator(User $validator)
    {
        if (!$this->validators->contains($validator)) {
            $this->validators->add($validator);
        }

        return $this;
    }

    public function removeValidator(User $validator)
    {
        if ($this->validators->contains($validator)) {
            $this->validators->removeElement($validator);
        }

        return $this;
    }

    public function emptyValidators()
    {
        $this->validators->clear();
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function hasValidation()
    {
        return $this->userValidation || $this->registrationValidation || $this->organizationValidation || count($this->getValidators()) > 0;
    }

    public function getEvents()
    {
        return $this->events->toArray();
    }

    public function getEventRegistrationType()
    {
        return $this->eventRegistrationType;
    }

    public function setEventRegistrationType($eventRegistrationType)
    {
        $this->eventRegistrationType = $eventRegistrationType;
    }

    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getColor()
    {
        return !is_null($this->details) && isset($this->details['color']) ? $this->details['color'] : null;
    }

    public function setColor($color)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['color'] = $color;
    }

    public function getTotal()
    {
        return !is_null($this->details) && isset($this->details['total']) ? $this->details['total'] : null;
    }

    public function setTotal($total)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['total'] = $total;
    }

    public function getCertificated()
    {
        return !is_null($this->details) && isset($this->details['certificated']) ? $this->details['certificated'] : true;
    }

    public function setCertificated($certificated)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['certificated'] = $certificated;
    }

    public function __toString()
    {
        return $this->getName();
    }
}

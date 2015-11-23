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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Cursus;
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

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api"})
     */
    protected $id;

    /**
     * @ORM\Column(name="session_name")
     * @Assert\NotBlank()
     * @Groups({"api"})
     * @SerializedName("name")
     */
    protected $name;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Course",
     *     inversedBy="sessions"
     * )
     * @ORM\JoinColumn(name="course_id", nullable=false, onDelete="CASCADE")
     */
    protected $course;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="SET NULL")
     */
    protected $workspace;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     * @ORM\JoinColumn(name="learner_role_id", nullable=true, onDelete="SET NULL")
     */
    protected $learnerRole;

    /**
     * @ORM\OneToOne(
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
     */
    protected $cursus;

    /**
     * @ORM\Column(name="session_status", type="integer")
     */
    protected $sessionStatus = self::SESSION_NOT_STARTED;

    /**
     * @ORM\Column(name="default_session", type="boolean")
     */
    protected $defaultSession = false;

    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="public_registration", type="boolean")
     */
    protected $publicRegistration = false;

    /**
     * @ORM\Column(name="public_unregistration", type="boolean")
     */
    protected $publicUnregistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     */
    protected $registrationValidation = false;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
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

    public function __construct()
    {
        $this->cursus = new ArrayCollection();
        $this->sessionUsers = new ArrayCollection();
        $this->sessionGroups = new ArrayCollection();
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

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace)
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

    public function removeCursu(Cursus $cursus)
    {
        if ($this->cursus->contains($cursus)) {
            $this->cursus->removeElement($cursus);
        }

        return $this;
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

    public function setLearnerRole(Role $learnerRole)
    {
        $this->learnerRole = $learnerRole;
    }

    public function getTutorRole()
    {
        return $this->tutorRole;
    }

    public function setTutorRole(Role $tutorRole)
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
        return $this->getCourseTitle() .
            ' [' .
            $this->getCourse()->getCode() .
            ']' .
            ' - ' .
            $this->getName();
    }

    public function getShortNameWithCourse($courseLength = 25)
    {
        $courseTitle = $this->getCourseTitle();
        $length = strlen($courseTitle);
        $shortTitle = ($length > $courseLength) ?
            substr($courseTitle, 0, $courseLength) . '...' :
            $courseTitle;



        return $shortTitle . ' - ' . $this->getName();
    }

    public function __toString()
    {
        return $this->getName();
    }
}

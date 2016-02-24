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

use Claroline\CoreBundle\Entity\Model\WorkspaceModel;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseRepository")
 * @ORM\Table(name="claro_cursusbundle_course")
 * @DoctrineAssert\UniqueEntity("code")
 */
class Course
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api", "bulletin"})
     */
    protected $id;
    
    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Groups({"api", "bulletin"})
     * @SerializedName("code")
     */
    protected $code;
    
    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api", "bulletin"})
     * @SerializedName("title")
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"api"})
     * @SerializedName("description")
     */
    protected $description;
    
    /**
     * @ORM\Column(name="public_registration", type="boolean")
     * @Groups({"api"})
     * @SerializedName("publicRegistration")
     */
    protected $publicRegistration = false;

    /**
     * @ORM\Column(name="public_unregistration", type="boolean")
     * @Groups({"api"})
     * @SerializedName("publicUnregistration")
     */
    protected $publicUnregistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     * @Groups({"api"})
     * @SerializedName("registrationValidation")
     */
    protected $registrationValidation = false;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Model\WorkspaceModel"
     * )
     * @ORM\JoinColumn(name="workspace_model_id", nullable=true, onDelete="SET NULL")
     */
    protected $workspaceModel;

    /**
     * @ORM\Column(name="tutor_role_name", nullable=true)
     */
    protected $tutorRoleName;

     /**
     * @ORM\Column(name="learner_role_name", nullable=true)
     */
    protected $learnerRoleName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession",
     *     mappedBy="course"
     * )
     * @Groups({"api"})
     */
    protected $sessions;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api"})
     * @SerializedName("icon")
     */
    protected $icon;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="SET NULL")
     */
    protected $workspace;

    /**
     * @ORM\Column(name="user_validation", type="boolean")
     * @Groups({"api"})
     * @SerializedName("userValidation")
     */
    protected $userValidation = false;

    /**
     * @ORM\Column(name="organization_validation", type="boolean")
     * @Groups({"api"})
     * @SerializedName("organizationValidation")
     */
    protected $organizationValidation = false;

    /**
     * @ORM\Column(name="max_users", nullable=true, type="integer")
     * @Groups({"api"})
     * @SerializedName("maxUsers")
     */
    protected $maxUsers;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinTable(name="claro_cursusbundle_course_validators")
     */
    protected $validators;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->validators = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
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

    public function getWorkspaceModel()
    {
        return $this->workspaceModel;
    }

    public function setWorkspaceModel(WorkspaceModel $workspaceModel = null)
    {
        $this->workspaceModel = $workspaceModel;
    }

    public function getTutorRoleName()
    {
        return $this->tutorRoleName;
    }

    public function setTutorRoleName($tutorRoleName)
    {
        $this->tutorRoleName = $tutorRoleName;
    }

    public function getLearnerRoleName()
    {
        return $this->learnerRoleName;
    }

    public function setLearnerRoleName($learnerRoleName)
    {
        $this->learnerRoleName = $learnerRoleName;
    }

    public function getSessions()
    {
        return $this->sessions->toArray();
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace = null)
    {
        $this->workspace = $workspace;
    }

    public function getUserValidation()
    {
        return $this->userValidation;
    }

    public function setUserValidation($userValidation)
    {
        $this->userValidation = $userValidation;
    }

    function getOrganizationValidation()
    {
        return $this->organizationValidation;
    }

    function setOrganizationValidation($organizationValidation)
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

    /**
     * Adds a validator to the course
     * @param \Claroline\CoreBundle\Entity\User $validator
     */
    public function addValidator(User $validator)
    {
        if (!$this->validators->contains($validator)) {
            $this->validators->add($validator);
        }

        return $this;
    }

    /**
     * Removes a validator from the course
     * @param \Claroline\CoreBundle\Entity\User $validator
     */
    public function removeValidator(User $validator)
    {
        if ($this->validators->contains($validator)) {
            $this->validators->removeElement($validator);
        }

        return $this;
    }

    public function hasValidation()
    {
        return $this->userValidation || $this->registrationValidation;
    }

    public function __toString()
    {
        return $this->getTitle() . ' [' . $this->getCode() . ']';
    }
}
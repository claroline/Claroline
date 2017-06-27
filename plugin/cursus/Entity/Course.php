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
use Claroline\CoreBundle\Entity\Organization\Organization;
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
    use UuidTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_cursus", "api_cursus_min", "api_bulletin", "api_user_min", "api_group_min", "api_workspace_min"})
     */
    protected $id;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     * @Groups({"api_cursus", "api_cursus_min", "api_bulletin", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("code")
     */
    protected $code;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     * @Groups({"api_cursus", "api_cursus_min", "api_bulletin", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("title")
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("description")
     */
    protected $description;

    /**
     * @ORM\Column(name="public_registration", type="boolean")
     * @Groups({"api", "api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("publicRegistration")
     */
    protected $publicRegistration = false;

    /**
     * @ORM\Column(name="public_unregistration", type="boolean")
     * @Groups({"api", "api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
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
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_model_id", nullable=true, onDelete="SET NULL")
     * @Groups({"api_user_min"})
     * @SerializedName("workspaceModel")
     */
    protected $workspaceModel;

    /**
     * @ORM\Column(name="tutor_role_name", nullable=true)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min"})
     * @SerializedName("tutorRoleName")
     */
    protected $tutorRoleName;

    /**
     * @ORM\Column(name="learner_role_name", nullable=true)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min"})
     * @SerializedName("learnerRoleName")
     */
    protected $learnerRoleName;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession",
     *     mappedBy="course"
     * )
     * @Groups({"api_cursus"})
     */
    protected $sessions;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("icon")
     */
    protected $icon;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=true, onDelete="SET NULL")
     * @Groups({"api_user_min"})
     */
    protected $workspace;

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
     * @ORM\JoinTable(name="claro_cursusbundle_course_validators")
     * @Groups({"api_user_min"})
     */
    protected $validators;

    /**
     * @ORM\Column(name="session_duration", nullable=false, type="integer", options={"default" = 1})
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("defaultSessionDuration")
     */
    protected $defaultSessionDuration = 1;

    /**
     * @ORM\Column(name="with_session_event", type="boolean", options={"default" = 1})
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("withSessionEvent")
     */
    private $withSessionEvent = true;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization"
     * )
     * @ORM\JoinTable(name="claro_cursusbundle_course_organizations")
     * @Groups({"api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("organizations")
     */
    protected $organizations;

    /**
     * @ORM\Column(name="display_order", type="integer", options={"default" = 500})
     * @Groups({"api_cursus", "api_cursus_min", "api_user_min", "api_group_min", "api_workspace_min"})
     * @SerializedName("displayOrder")
     */
    protected $displayOrder = 500;

    public function __construct()
    {
        $this->sessions = new ArrayCollection();
        $this->validators = new ArrayCollection();
        $this->organizations = new ArrayCollection();
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

    public function setWorkspaceModel(Workspace $workspace = null)
    {
        $this->workspaceModel = $workspace;
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

    public function hasValidation()
    {
        return $this->userValidation || $this->registrationValidation;
    }

    public function getDefaultSessionDuration()
    {
        return $this->defaultSessionDuration;
    }

    public function setDefaultSessionDuration($defaultSessionDuration)
    {
        $this->defaultSessionDuration = $defaultSessionDuration;
    }

    public function getWithSessionEvent()
    {
        return $this->withSessionEvent;
    }

    public function setWithSessionEvent($withSessionEvent)
    {
        $this->withSessionEvent = $withSessionEvent;
    }

    public function getOrganizations()
    {
        return $this->organizations->toArray();
    }

    public function addOrganization(Organization $organization)
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization)
    {
        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
        }

        return $this;
    }

    public function emptyOrganizations()
    {
        $this->organizations->clear();
    }

    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder($displayOrder)
    {
        $this->displayOrder = $displayOrder;
    }

    public function __toString()
    {
        return $this->getTitle().' ['.$this->getCode().']';
    }
}

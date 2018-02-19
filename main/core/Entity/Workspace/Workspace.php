<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Workspace;

use Claroline\CoreBundle\Entity\Model\OrganizationsTrait;
use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRepository")
 * @ORM\Table(name="claro_workspace", indexes={@ORM\Index(name="name_idx", columns={"name"})})
 * @DoctrineAssert\UniqueEntity("code")
 */
class Workspace
{
    use OrganizationsTrait;
    use UuidTrait;

    const DEFAULT_MAX_STORAGE_SIZE = '1 TB';
    const DEFAULT_MAX_FILE_COUNT = 10000;
    const DEFAULT_MAX_USERS = 10000;

    protected static $visitorPrefix = 'ROLE_WS_VISITOR';
    protected static $collaboratorPrefix = 'ROLE_WS_COLLABORATOR';
    protected static $managerPrefix = 'ROLE_WS_MANAGER';
    protected static $customPrefix = 'ROLE_WS_CUSTOM';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"api_workspace", "api_workspace_min", "api_user_min", "api_user"})
     * @Serializer\SerializedName("id")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column()
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min", "api_user_min"})
     * @Serializer\SerializedName("name")
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("description")
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank()
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min", "api_user_min"})
     * @Serializer\SerializedName("code")
     *
     * @var string
     */
    protected $code;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("maxStorageSize")
     *
     * @var string
     */
    protected $maxStorageSize = '1 TB';

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("maxUploadResources")
     *
     * @var int
     */
    protected $maxUploadResources = 10000;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("maxUsers")
     *
     * @var int
     */
    protected $maxUsers = 10000;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("displayable")
     *
     * @var bool
     */
    protected $displayable = false;

    /**
     * @ORM\Column(name="isModel", type="boolean")
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("isModel")
     *
     * @var bool
     */
    protected $model = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode",
     *     mappedBy="workspace"
     * )
     *
     * @var ResourceNode[]|ArrayCollection
     */
    protected $resources;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     mappedBy="workspace",
     *     cascade={"persist", "merge"}
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     *
     * @var OrderedTool[]|ArrayCollection
     */
    protected $orderedTools;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     mappedBy="workspace",
     *     cascade={"persist", "merge"}
     * )
     *
     * @var Role[]|ArrayCollection
     */
    protected $roles;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL")
     *
     * @Serializer\SerializedName("creator")
     *
     * @var User
     */
    protected $creator;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("selfRegistration")
     *
     * @var bool
     */
    protected $selfRegistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("registrationValidation")
     *
     * @var bool
     */
    protected $registrationValidation = false;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("selfUnregistration")
     *
     * @var bool
     */
    protected $selfUnregistration = false;

    /**
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("creationDate")
     * @Serializer\Accessor(getter="getCreationDate")
     * @Serializer\Type("DateTime<'Y-m-d\TH:i:s'>")
     * @Gedmo\Timestampable(on="create")
     *
     * @var \DateTime
     */
    protected $created;

    /**
     * @ORM\Column(name="is_personal", type="boolean")
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("isPersonal")
     *
     * @var bool
     */
    protected $personal = false;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("startDate")
     *
     * @var \DateTime
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("endDate")
     *
     * @var \DateTime
     */
    protected $endDate;

    /**
     * @ORM\Column(name="is_access_date", type="boolean")
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("isAccessDate")
     *
     * @var bool
     */
    protected $isAccessDate = false;

    /**
     * @ORM\Column(name="workspace_type", type="integer", nullable=true)
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("workspaceType")
     *
     * @var int
     */
    protected $workspaceType;

    /**
     * @ORM\Column(name="disabled_notifications", type="boolean")
     *
     * @Serializer\Groups({"api_workspace", "api_workspace_min"})
     * @Serializer\SerializedName("disabledNotifications")
     *
     * @var bool
     */
    protected $disabledNotifications = false;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions",
     *     inversedBy="workspace"
     * )
     * @ORM\JoinColumn(name="options_id", onDelete="SET NULL", nullable=true)
     *
     * @var WorkspaceOptions
     */
    protected $options;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     mappedBy="personalWorkspace",
     *     cascade={"persist"}
     * )
     *
     * @var User
     */
    protected $personalUser;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     inversedBy="workspaces"
     * )
     *
     * @var ArrayCollection
     */
    protected $organizations;

    /**
     * Workspace constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->orderedTools = new ArrayCollection();
        $this->organizations = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name.' ['.$this->code.']';
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description ? $this->description : '';
    }

    /**
     * Set description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get resources.
     *
     * @return ResourceNode[]|ArrayCollection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set code.
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get ordered tools.
     *
     * @return OrderedTool[]|ArrayCollection
     */
    public function getOrderedTools()
    {
        return $this->orderedTools;
    }

    /**
     * Add an ordered tool.
     *
     * @param OrderedTool $tool
     */
    public function addOrderedTool(OrderedTool $tool)
    {
        $this->orderedTools->add($tool);
    }

    /**
     * Remove an ordered tool.
     *
     * @param OrderedTool $tool
     */
    public function removeOrderedTool(OrderedTool $tool)
    {
        $this->orderedTools->removeElement($tool);
    }

    /**
     * Get roles.
     *
     * @return Role[]|ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Add a role.
     *
     * @param Role $role
     */
    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * Remove a role.
     *
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * Get creator.
     *
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set creator.
     *
     * @param User $creator
     */
    public function setCreator(User $creator = null)
    {
        $this->creator = $creator;
    }

    /**
     * Set guid.
     *
     * @param string $guid
     */
    public function setGuid($guid)
    {
        $this->uuid = $guid;
    }

    /**
     * Get guid.
     *
     * @return string
     */
    public function getGuid()
    {
        return $this->uuid;
    }

    /**
     * Set displayable.
     *
     * @param bool $displayable
     */
    public function setDisplayable($displayable)
    {
        $this->displayable = $displayable;
    }

    /**
     * Is displayable ?
     *
     * @return bool
     */
    public function isDisplayable()
    {
        return $this->displayable;
    }

    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }

    public function getRegistrationValidation()
    {
        return $this->registrationValidation;
    }

    public function setRegistrationValidation($registrationValidation)
    {
        $this->registrationValidation = $registrationValidation;
    }

    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    public function getSelfUnregistration()
    {
        return $this->selfUnregistration;
    }

    /**
     * @param $creationDate
     *
     * @deprecated use `setCreated()` instead
     */
    public function setCreationDate($creationDate)
    {
        $this->setCreated($creationDate);
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return \Datetime
     *
     * @deprecated use `getCreated()` instead
     */
    public function getCreationDate()
    {
        return $this->getCreated();
    }

    /**
     * @todo internal implementation should only return the prop. I don't know why it works like this
     *
     * @return \Datetime
     */
    public function getCreated()
    {
        $date = !is_null($this->created) ? date('d-m-Y H:i', $this->created) : null;

        return new \Datetime($date);
    }

    /**
     * Sets how many MB can be stored in the workspace.
     *
     * @param $maxSize
     */
    public function setMaxStorageSize($maxSize)
    {
        $this->maxStorageSize = $maxSize;
    }

    /**
     * Returns how many MB can be stored in the workspace.
     *
     * @return int
     */
    public function getMaxStorageSize()
    {
        return $this->maxStorageSize;
    }

    public function setMaxUploadResources($maxSize)
    {
        $this->maxUploadResources = $maxSize;
    }

    public function getMaxUploadResources()
    {
        return $this->maxUploadResources;
    }

    /**
     * @param $isPersonal
     *
     * @deprecated use `setPersonal()` instead
     */
    public function setIsPersonal($isPersonal)
    {
        $this->setPersonal($isPersonal);
    }

    public function setPersonal($personal)
    {
        $this->personal = $personal;
    }

    public function isPersonal()
    {
        return $this->personal;
    }

    public function serializeForWidgetPicker()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
    }

    public function getIsAccessDate()
    {
        return $this->isAccessDate;
    }

    public function setIsAccessDate($isAccessDate)
    {
        $this->isAccessDate = $isAccessDate;
    }

    public function getWorkspaceType()
    {
        return $this->workspaceType;
    }

    public function setWorkspaceType($workspaceType)
    {
        $this->workspaceType = $workspaceType;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function getNameAndCode()
    {
        return $this->name.' ['.$this->code.']';
    }

    /**
     * @return \Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(WorkspaceOptions $options = null)
    {
        $this->options = $options;
    }

    /**
     * @return null|string
     */
    public function getBackgroundColor()
    {
        $backgroundColor = null;
        $workspaceOptions = $this->getOptions();

        if (null !== $workspaceOptions) {
            $workspaceOptionsDetails = $workspaceOptions->getDetails();

            if (isset($workspaceOptionsDetails['background_color'])) {
                $backgroundColor = $workspaceOptionsDetails['background_color'];
            }
        }

        return $backgroundColor;
    }

    public function getManagerRole()
    {
        foreach ($this->roles as $role) {
            if (1 === strpos('_'.$role->getName(), 'ROLE_WS_MANAGER')) {
                return $role;
            }
        }

        return null;
    }

    public function getPersonalUser()
    {
        return $this->personalUser;
    }

    /**
     * @param $boolean
     *
     * @deprecated use `setModel()` instead
     */
    public function setIsModel($boolean)
    {
        $this->setModel($boolean);
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function isModel()
    {
        return $this->model;
    }

    /**
     * @return bool
     */
    public function isDisabledNotifications()
    {
        return $this->disabledNotifications;
    }

    /**
     * @param bool $disabledNotifications
     */
    public function setDisabledNotifications($disabledNotifications)
    {
        $this->disabledNotifications = $disabledNotifications;
    }
}

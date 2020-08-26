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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\AppBundle\Entity\Restriction\AccessCode;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\AppBundle\Entity\Restriction\AllowedIps;
use Claroline\CoreBundle\Entity\Model\OrganizationsTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRepository")
 * @ORM\Table(name="claro_workspace", indexes={
 *     @ORM\Index(name="name_idx", columns={"name"})
 * })
 */
class Workspace
{
    // identifiers
    use Id;
    use Uuid;

    // meta
    use Poster;
    use Thumbnail;
    use Description;
    use Creator;

    // restrictions
    use AccessibleFrom;
    use AccessibleUntil;
    use AccessCode;
    use AllowedIps;

    use OrganizationsTrait;

    /**
     * The name of the workspace.
     *
     * @ORM\Column()
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $name;

    /**
     * The code of the workspace.
     *
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $code;

    /**
     * @Gedmo\Slug(fields={"code"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", nullable=false)
     *
     * @var string
     */
    protected $maxStorageSize = '1 TB';

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $lang = null;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $maxUploadResources = 10000;

    /**
     * @ORM\Column(type="integer", nullable=false)
     *
     * @var int
     */
    protected $maxUsers = 10000;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var bool
     */
    protected $displayable = true;

    /**
     * @ORM\Column(name="isModel", type="boolean")
     *
     * @var bool
     */
    protected $model = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\OrderedTool",
     *     mappedBy="workspace",
     *     cascade={"persist", "merge"}
     * )
     * @ORM\OrderBy({"order" = "ASC"})
     *
     * @var OrderedTool[]|ArrayCollection
     *
     * @todo : remove me. relation should be unidirectional
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
     *
     * @todo : remove me. relation should be unidirectional
     */
    protected $roles;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="default_role_id", onDelete="SET NULL")
     *
     * @var User
     */
    protected $defaultRole;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     *
     * @var bool
     */
    protected $selfRegistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     *
     * @var bool
     */
    protected $registrationValidation = false;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     *
     * @var bool
     */
    protected $selfUnregistration = false;

    /**
     * @ORM\Column(name="creation_date", type="integer", nullable=true)
     * @Gedmo\Timestampable(on="create")
     *
     * @todo store a DateTime and remove Gedmo (can be handled by Crud)
     *
     * @var \DateTime
     */
    protected $created;

    /**
     * @ORM\Column(name="is_personal", type="boolean")
     *
     * @var bool
     */
    protected $personal = false;

    /**
     * @ORM\Column(name="workspace_type", type="integer", nullable=true)
     *
     * @var int
     */
    protected $workspaceType;

    /**
     * @ORM\Column(name="disabled_notifications", type="boolean")
     *
     * @var bool
     */
    protected $disabledNotifications = false;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions",
     *     inversedBy="workspace",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="options_id", onDelete="SET NULL", nullable=true)
     *
     * @var WorkspaceOptions
     */
    protected $options;

    /**
     * Display user progression when the workspace is rendered.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $showProgression = true;

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

    //not mapped. Used for creation
    private $workspaceModel;

    /**
     * @ORM\Column(name="archived", type="boolean")
     *
     * @var bool
     */
    protected $archived = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Shortcuts",
     *     mappedBy="workspace"
     * )
     *
     * @var Shortcuts[]|ArrayCollection
     */
    protected $shortcuts;

    /**
     * Workspace constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->roles = new ArrayCollection();
        $this->orderedTools = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->options = new WorkspaceOptions();
        $this->shortcuts = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name.' ['.$this->code.']';
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
     * Set guid.
     *
     * @param string $guid
     *
     * @deprecated use setUuid()
     */
    public function setGuid($guid)
    {
        $this->uuid = $guid;
    }

    /**
     * Get guid.
     *
     * @return string
     *
     * @deprecated use getUuid()
     */
    public function getGuid()
    {
        return $this->uuid;
    }

    /**
     * Set displayable.
     *
     * @param bool $displayable
     *
     * @deprecated use `setHidden()`
     */
    public function setDisplayable($displayable)
    {
        $this->displayable = $displayable;
    }

    /**
     * Is displayable ?
     *
     * @deprecated use `isHidden()`
     *
     * @return bool
     */
    public function isDisplayable()
    {
        return $this->displayable;
    }

    /**
     * Is hidden ?
     *
     * @return bool
     */
    public function isHidden()
    {
        return !$this->displayable;
    }

    /**
     * Set hidden.
     *
     * @param bool $hidden
     */
    public function setHidden($hidden)
    {
        $this->displayable = !$hidden;
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
     * @return \Datetime
     */
    public function getCreated()
    {
        // todo : change column to datetime to avoid this
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

    public function setNotifications($notifications)
    {
        $this->disabledNotifications = !$notifications;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function setDefaultRole(Role $role)
    {
        $this->defaultRole = $role;
    }

    public function getDefaultRole()
    {
        if (!$this->defaultRole) {
            foreach ($this->roles as $role) {
                if (strpos($role->getName(), 'COLLABORATOR')) {
                    return $role;
                }
            }

            return $this->roles[0];
        }

        return $this->defaultRole;
    }

    public function setWorkspaceModel(self $model)
    {
        $this->workspaceModel = $model;
    }

    public function getWorkspaceModel()
    {
        return $this->workspaceModel;
    }

    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function getShowProgression()
    {
        return $this->showProgression;
    }

    public function setShowProgression($showProgression)
    {
        $this->showProgression = $showProgression;
    }

    public function setArchived($archived)
    {
        $this->archived = $archived;
    }

    public function isArchived()
    {
        return $this->archived;
    }

    /**
     * Get shortcuts.
     *
     * @return Shortcuts[]|ArrayCollection
     */
    public function getShortcuts()
    {
        return $this->shortcuts;
    }

    public function addShortcuts(Shortcuts $shortcuts)
    {
        if (!$this->shortcuts->contains($shortcuts)) {
            $this->shortcuts->add($shortcuts);
        }
    }

    public function removeShortcuts(Shortcuts $shortcuts)
    {
        if ($this->shortcuts->contains($shortcuts)) {
            $this->shortcuts->removeElement($shortcuts);
        }
    }
}

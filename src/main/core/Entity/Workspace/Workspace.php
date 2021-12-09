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

use Claroline\AppBundle\Entity\IdentifiableInterface;
use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\AppBundle\Entity\Restriction\AccessCode;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\AppBundle\Entity\Restriction\AllowedIps;
use Claroline\AppBundle\Entity\Restriction\Hidden;
use Claroline\CoreBundle\Entity\Model\OrganizationsTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRepository")
 * @ORM\Table(name="claro_workspace", indexes={
 *     @ORM\Index(name="name_idx", columns={"entity_name"})
 * })
 */
class Workspace implements IdentifiableInterface
{
    // identifiers
    use Id;
    use Uuid;
    use Code;
    // meta
    use Name;
    use Poster;
    use Thumbnail;
    use Description;
    use Creator;
    use CreatedAt;
    use UpdatedAt;
    // restrictions
    use Hidden;
    use AccessibleFrom;
    use AccessibleUntil;
    use AccessCode;
    use AllowedIps;
    use OrganizationsTrait;

    /**
     * @Gedmo\Slug(fields={"code"})
     * @ORM\Column(length=128, unique=true)
     *
     * @var string
     */
    private $slug;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $lang = null;

    /**
     * @ORM\Column(name="isModel", type="boolean")
     *
     * @var bool
     */
    private $model = false;

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
    private $orderedTools;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     mappedBy="workspace",
     *     cascade={"persist", "merge"}
     * )
     *
     * @var Role[]|ArrayCollection
     */
    private $roles;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="default_role_id", onDelete="SET NULL")
     *
     * @var Role
     */
    private $defaultRole;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     *
     * @var bool
     */
    private $selfRegistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     *
     * @var bool
     */
    private $registrationValidation = false;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     *
     * @var bool
     */
    private $selfUnregistration = false;

    /**
     * @ORM\Column(name="is_personal", type="boolean")
     *
     * @var bool
     */
    private $personal = false;

    /**
     * @ORM\Column(name="disabled_notifications", type="boolean")
     *
     * @var bool
     */
    private $disabledNotifications = false;

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
    private $options;

    /**
     * Display user progression when the workspace is rendered.
     *
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $showProgression = true;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $contactEmail;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     inversedBy="workspaces"
     * )
     *
     * @var ArrayCollection
     */
    private $organizations;

    //not mapped. Used for creation
    private $workspaceModel;

    /**
     * @ORM\Column(name="archived", type="boolean")
     *
     * @var bool
     */
    private $archived = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Shortcuts",
     *     mappedBy="workspace"
     * )
     *
     * @var Shortcuts[]|ArrayCollection
     */
    private $shortcuts;

    public function __construct()
    {
        $this->refreshUuid();

        $this->roles = new ArrayCollection();
        $this->orderedTools = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->options = new WorkspaceOptions();
        $this->shortcuts = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name.' ['.$this->code.']';
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug)
    {
        $this->slug = $slug;
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

    public function addOrderedTool(OrderedTool $tool)
    {
        $this->orderedTools->add($tool);
    }

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

    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
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

    public function setPersonal(bool $personal)
    {
        $this->personal = $personal;
    }

    public function isPersonal(): bool
    {
        return $this->personal;
    }

    /**
     * @return WorkspaceOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions(WorkspaceOptions $options = null)
    {
        $this->options = $options;
    }

    public function getManagerRole(): ?Role
    {
        foreach ($this->roles as $role) {
            if (1 === strpos('_'.$role->getName(), 'ROLE_WS_MANAGER')) {
                return $role;
            }
        }

        return null;
    }

    public function getCollaboratorRole(): ?Role
    {
        foreach ($this->roles as $role) {
            if (1 === strpos('_'.$role->getName(), 'ROLE_WS_COLLABORATOR')) {
                return $role;
            }
        }

        return null;
    }

    public function setModel(bool $model)
    {
        $this->model = $model;
    }

    public function isModel(): bool
    {
        return $this->model;
    }

    public function hasNotifications(): bool
    {
        return !$this->disabledNotifications;
    }

    public function setNotifications(bool $notifications)
    {
        $this->disabledNotifications = !$notifications;
    }

    public function setDefaultRole(?Role $role = null)
    {
        $this->defaultRole = $role;
    }

    public function getDefaultRole(): ?Role
    {
        if (!$this->defaultRole && !empty($this->roles)) {
            $collaborator = $this->getCollaboratorRole();
            if ($collaborator) {
                return $collaborator;
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

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(?string $email = null)
    {
        $this->contactEmail = $email;
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

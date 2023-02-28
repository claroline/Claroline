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
use Claroline\AppBundle\Entity\Meta\Archived;
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
use Claroline\CommunityBundle\Model\HasOrganizations;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    use Archived;
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
    use HasOrganizations;

    /**
     * @Gedmo\Slug(fields={"code"})
     * @ORM\Column(length=128, unique=true)
     *
     * @var string
     */
    private $slug;

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
     * @deprecated relation should be unidirectional
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
     *
     * @deprecated relation should be unidirectional (not sure it's possible to remove it)
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
     *
     * @deprecated to move in community parameters
     */
    private $defaultRole;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     *
     * @var bool
     *
     * @deprecated to move in community parameters
     */
    private $selfRegistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     *
     * @var bool
     *
     * @deprecated to move in community parameters
     */
    private $registrationValidation = false;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     *
     * @var bool
     *
     * @deprecated to move in community parameters
     */
    private $selfUnregistration = false;

    /**
     * @ORM\Column(name="max_teams", type="integer", nullable=true)
     *
     * @var int
     *
     * @deprecated to move in community parameters
     */
    private $maxTeams;

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
     * The conditions to get a success status for the workspace evaluation.
     * Supported conditions : minimal score, min successful resources, max failed resources.
     *
     * @var array
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private $successCondition = null;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\Organization",
     *     inversedBy="workspaces"
     * )
     *
     * @var Collection|Organization[]
     */
    private Collection $organizations;

    //not mapped. Used for creation
    private $workspaceModel;

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
     *
     * @deprecated
     */
    public function getOrderedTools()
    {
        return $this->orderedTools;
    }

    /**
     * @deprecated
     */
    public function addOrderedTool(OrderedTool $tool)
    {
        $this->orderedTools->add($tool);
    }

    /**
     * @deprecated
     */
    public function removeOrderedTool(OrderedTool $tool)
    {
        $this->orderedTools->removeElement($tool);
    }

    /**
     * Get roles.
     *
     * @return Role[]|ArrayCollection
     *
     * @deprecated
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @deprecated
     */
    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * @deprecated
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * @deprecated
     */
    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    /**
     * @deprecated
     */
    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }

    /**
     * @deprecated
     */
    public function getRegistrationValidation()
    {
        return $this->registrationValidation;
    }

    /**
     * @deprecated
     */
    public function setRegistrationValidation($registrationValidation)
    {
        $this->registrationValidation = $registrationValidation;
    }

    /**
     * @deprecated
     */
    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    /**
     * @deprecated
     */
    public function getSelfUnregistration()
    {
        return $this->selfUnregistration;
    }

    /**
     * @deprecated
     */
    public function getMaxTeams(): ?int
    {
        return $this->maxTeams;
    }

    /**
     * @deprecated
     */
    public function setMaxTeams(?int $maxTeams = null): void
    {
        $this->maxTeams = $maxTeams;
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

    /**
     * @deprecated
     */
    public function setDefaultRole(?Role $role = null)
    {
        $this->defaultRole = $role;
    }

    /**
     * @deprecated
     */
    public function getDefaultRole(): ?Role
    {
        if (!$this->defaultRole && 0 !== $this->roles->count()) {
            $collaborator = $this->getCollaboratorRole();
            if ($collaborator) {
                return $collaborator;
            }

            return $this->roles->first();
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

    public function getSuccessCondition(): ?array
    {
        return $this->successCondition;
    }

    public function setSuccessCondition(?array $successCondition)
    {
        $this->successCondition = $successCondition;
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

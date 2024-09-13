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

use Claroline\AppBundle\Component\Context\ContextSubjectInterface;
use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Hidden;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Archived;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\DescriptionHtml;
use Claroline\AppBundle\Entity\Meta\IsPublic;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\AppBundle\Entity\Restriction\AccessCode;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\AppBundle\Entity\Restriction\AllowedIps;
use Claroline\CommunityBundle\Model\HasOrganizations;
use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\WorkspaceRepository")
 *
 * @ORM\Table(name="claro_workspace", indexes={@ORM\Index(name="name_idx", columns={"entity_name"})})
 */
class Workspace implements ContextSubjectInterface, CrudEntityInterface
{
    // identifiers
    use Id;
    use Uuid;
    use Code;
    // meta
    use Archived;
    use Name;
    use IsPublic;
    use Description;
    use DescriptionHtml;
    use Creator;
    use CreatedAt;
    use UpdatedAt;
    // display
    use Hidden;
    use Poster;
    use Thumbnail;
    // restrictions
    use AccessibleFrom;
    use AccessibleUntil;
    use AccessCode;
    use AllowedIps;
    use HasOrganizations;

    /**
     * @Gedmo\Slug(fields={"code"})
     *
     * @ORM\Column(length=128, unique=true)
     */
    private ?string $slug = null;

    /**
     * @ORM\Column(name="isModel", type="boolean")
     */
    private bool $model = false;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Role",
     *     mappedBy="workspace",
     *     fetch="EXTRA_LAZY"
     * )
     */
    private Collection $roles;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     *
     * @ORM\JoinColumn(name="default_role_id", onDelete="SET NULL")
     */
    private ?Role $defaultRole;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     *
     * @deprecated to move in community parameters
     */
    private bool $selfRegistration = false;

    /**
     * @ORM\Column(name="registration_validation", type="boolean")
     *
     * @deprecated to move in community parameters
     */
    private bool $registrationValidation = false;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     *
     * @deprecated to move in community parameters
     */
    private bool $selfUnregistration = false;

    /**
     * @ORM\Column(name="max_teams", type="integer", nullable=true)
     *
     * @deprecated to move in community parameters
     */
    private ?int $maxTeams = null;

    /**
     * @ORM\Column(name="is_personal", type="boolean")
     */
    private bool $personal = false;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\WorkspaceOptions",
     *     inversedBy="workspace",
     *     cascade={"persist"}
     * )
     *
     * @ORM\JoinColumn(name="options_id", onDelete="SET NULL", nullable=true)
     */
    private WorkspaceOptions $options;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $contactEmail = null;

    /**
     * The conditions to get a success status for the workspace evaluation.
     * Supported conditions : minimal score, min successful resources, max failed resources.
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $successCondition = [];

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization")
     *
     * @ORM\JoinTable(name="workspace_organization")
     */
    private Collection $organizations;

    // not mapped. Used for creation
    private $workspaceModel;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $estimatedDuration = null;

    /**
     * @ORM\Column(name="score_total", type="float", options={"default" = 100})
     */
    private float $scoreTotal = 100;

    public function __construct()
    {
        $this->refreshUuid();

        $this->roles = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->options = new WorkspaceOptions();
    }

    public static function getIdentifiers(): array
    {
        return ['code', 'slug'];
    }

    public function __toString(): string
    {
        return $this->name.' ['.$this->code.']';
    }

    public function getContextIdentifier(): string
    {
        return $this->uuid;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @deprecated
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * @deprecated
     */
    public function addRole(Role $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    /**
     * @deprecated
     */
    public function removeRole(Role $role): void
    {
        $this->roles->removeElement($role);
    }

    /**
     * @deprecated
     */
    public function setSelfRegistration($selfRegistration): void
    {
        $this->selfRegistration = $selfRegistration;
    }

    /**
     * @deprecated
     */
    public function getSelfRegistration(): bool
    {
        return $this->selfRegistration;
    }

    /**
     * @deprecated
     */
    public function getRegistrationValidation(): bool
    {
        return $this->registrationValidation;
    }

    /**
     * @deprecated
     */
    public function setRegistrationValidation(bool $registrationValidation): void
    {
        $this->registrationValidation = $registrationValidation;
    }

    /**
     * @deprecated
     */
    public function setSelfUnregistration(bool $selfUnregistration): void
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    /**
     * @deprecated
     */
    public function getSelfUnregistration(): bool
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
    public function setMaxTeams(int $maxTeams = null): void
    {
        $this->maxTeams = $maxTeams;
    }

    public function setPersonal(bool $personal): void
    {
        $this->personal = $personal;
    }

    public function isPersonal(): bool
    {
        return $this->personal;
    }

    public function getOptions(): ?WorkspaceOptions
    {
        return $this->options;
    }

    public function setOptions(WorkspaceOptions $options = null): void
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

    public function setModel(bool $model): void
    {
        $this->model = $model;
    }

    public function isModel(): bool
    {
        return $this->model;
    }

    /**
     * @deprecated
     */
    public function setDefaultRole(Role $role = null): void
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

    public function setWorkspaceModel(self $model): void
    {
        $this->workspaceModel = $model;
    }

    public function getWorkspaceModel(): ?self
    {
        return $this->workspaceModel;
    }

    public function getContactEmail(): ?string
    {
        return $this->contactEmail;
    }

    public function setContactEmail(string $email = null): void
    {
        $this->contactEmail = $email;
    }

    public function getSuccessCondition(): ?array
    {
        return $this->successCondition;
    }

    public function setSuccessCondition(?array $successCondition): void
    {
        $this->successCondition = $successCondition;
    }

    public function getEstimatedDuration(): ?int
    {
        return $this->estimatedDuration;
    }

    public function setEstimatedDuration(int $estimatedDuration = null): void
    {
        $this->estimatedDuration = $estimatedDuration;
    }

    public function getScoreTotal(): ?float
    {
        return $this->scoreTotal;
    }

    public function setScoreTotal(float $scoreTotal = null): void
    {
        $this->scoreTotal = $scoreTotal;
    }
}

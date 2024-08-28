<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Organization;

use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\IsPublic;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid as BaseUuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CommunityBundle\Repository\OrganizationRepository")
 *
 * @ORM\Table(name="claro__organization")
 *
 * @Gedmo\Tree(type="nested")
 */
class Organization implements CrudEntityInterface
{
    use Code;
    use Id;
    use Uuid;
    use Name;
    use IsPublic;
    use Description;
    use Poster;
    use Thumbnail;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $position = null;

    /**
     * @ORM\Column(nullable=true, type="string")
     *
     * @Assert\Email()
     */
    private ?string $email = null;

    /**
     * @Gedmo\TreeLeft
     *
     * @ORM\Column(name="lft", type="integer")
     */
    protected ?int $lft = null;

    /**
     * @Gedmo\TreeLevel
     *
     * @ORM\Column(name="lvl", type="integer")
     */
    protected ?int $lvl = null;

    /**
     * @Gedmo\TreeRight
     *
     * @ORM\Column(name="rgt", type="integer")
     */
    protected ?int $rgt = null;

    /**
     * @Gedmo\TreeRoot
     *
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected ?int $root = null;

    /**
     * @Gedmo\TreeParent
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization", inversedBy="children")
     *
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?Organization $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization", mappedBy="parent")
     *
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private Collection $children;

    /**
     * @ORM\Column(name="is_default", type="boolean")
     */
    private bool $default = false;

    public function __construct()
    {
        $this->refreshUuid();
        // todo : generate unique from name for a more beautiful code
        $this->code = BaseUuid::uuid4()->toString();
        $this->children = new ArrayCollection();
    }

    public static function getIdentifiers(): array
    {
        return ['code'];
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function setPosition(?int $position = null): void
    {
        $this->position = $position;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function getParent(): ?Organization
    {
        return $this->parent;
    }

    public function setParent(?Organization $parent = null): void
    {
        $this->parent = $parent;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getRoot(): ?int
    {
        return $this->root;
    }

    public function getLeft(): ?int
    {
        return $this->lft;
    }

    public function getRight(): ?int
    {
        return $this->rgt;
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::HasUsersTrait.
     */
    public function addUser(User $user): void
    {
        $user->addOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::HasUsersTrait.
     */
    public function removeUser(User $user): void
    {
        $user->removeOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::addManagersAction.
     */
    public function addManager(User $user): void
    {
        $user->addAdministratedOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::removeManagersAction.
     */
    public function removeManager(User $user): void
    {
        $user->removeAdministratedOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::HasGroupsTrait.
     */
    public function addGroup(Group $group): void
    {
        $group->addOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by TransferFeature and OrganizationController::HasGroupsTrait.
     */
    public function removeGroup(Group $group): void
    {
        $group->removeOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by OrganizationController::HasGroupsTrait.
     */
    public function addWorkspace(Workspace $workspace): void
    {
        $workspace->addOrganization($this);
    }

    /**
     * @deprecated no replacement. Required by OrganizationController::HasWorkspacesTrait.
     */
    public function removeWorkspace(Workspace $workspace): void
    {
        $workspace->removeOrganization($this);
    }
}

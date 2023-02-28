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

use Claroline\AppBundle\Entity\Identifier\Code;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\AppBundle\Entity\Meta\Poster;
use Claroline\AppBundle\Entity\Meta\Thumbnail;
use Claroline\CommunityBundle\Model\HasGroups;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid as BaseUuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CommunityBundle\Repository\OrganizationRepository")
 * @ORM\Table(name="claro__organization")
 * @Gedmo\Tree(type="nested")
 */
class Organization
{
    use Code;
    use Id;
    use Uuid;
    use Name;
    use Description;
    use Poster;
    use Thumbnail;
    use HasGroups;

    const TYPE_EXTERNAL = 'external';
    const TYPE_INTERNAL = 'internal';

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $position;

    /**
     * @ORM\Column(nullable=true, type="string")
     * @Assert\Email()
     *
     * @var string
     */
    private $email;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Location\Location",
     *     cascade={"persist"},
     *     inversedBy="organizations"
     * )
     * @ORM\JoinTable(name="claro__location_organization")
     *
     * @var ArrayCollection
     *
     * @deprecated Should be unidirectional. Still used in badges
     */
    private $locations;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     *
     * @var int
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     *
     * @var int
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     *
     * @var int
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     *
     * @var int
     */
    protected $root;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var Organization
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization", mappedBy="parent")
     * @ORM\OrderBy({"name" = "ASC"})
     *
     * @var Organization[]|ArrayCollection
     */
    private $children;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     mappedBy="organizations"
     * )
     * @ORM\JoinTable(name="claro_user_workspace")
     *
     * @var Workspace[]|ArrayCollection
     *
     * @deprecated should be unidirectional
     */
    private $workspaces;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Group",
     *     mappedBy="organizations"
     * )
     * @ORM\JoinTable(name="claro_group_organization")
     *
     * @var ArrayCollection
     *
     * @deprecated should be unidirectional
     */
    private $groups;

    /**
     * @ORM\Column(name="is_default", type="boolean")
     */
    private $default = false;

    /**
     * @ORM\Column(name="is_public", type="boolean")
     */
    private $public = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $vat;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $type = self::TYPE_INTERNAL;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\UserOrganizationReference",
     *     mappedBy="organization",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="organization_id", nullable=false)
     *
     * @var ArrayCollection|UserOrganizationReference[]
     *
     * @deprecated should be unidirectional
     */
    private $userOrganizationReferences;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Cryptography\CryptographicKey", mappedBy="organization")
     */
    private $keys;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $maxUsers = -1;

    public function __construct()
    {
        $this->refreshUuid();
        // todo : generate unique from name for a more beautiful code
        $this->code = BaseUuid::uuid4()->toString();

        $this->locations = new ArrayCollection();
        $this->workspaces = new ArrayCollection();
        $this->keys = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->userOrganizationReferences = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString()
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

    /**
     * @deprecated
     */
    public function setLocations($locations): void
    {
        $this->locations = $locations;
    }

    /**
     * @deprecated
     */
    public function getLocations()
    {
        return $this->locations;
    }

    /**
     * @deprecated
     */
    public function addLocation(Location $location)
    {
        return $this->locations->add($location);
    }

    /**
     * @deprecated
     */
    public function removeLocation(Location $location)
    {
        return $this->locations->removeElement($location);
    }

    public function getManagers(): ArrayCollection
    {
        $managers = new ArrayCollection();
        foreach ($this->userOrganizationReferences as $userRef) {
            if ($userRef->isManager()) {
                $managers->add($userRef->getUser());
            }
        }

        return $managers;
    }

    public function hasManager(User $user): bool
    {
        foreach ($this->userOrganizationReferences as $userRef) {
            if ($userRef->isManager() && $user->getId() === $userRef->getUser()->getId()) {
                return true;
            }
        }

        return false;
    }

    public function addManager(User $user): void
    {
        $this->addUser($user, true);
    }

    public function removeManager(User $user): void
    {
        foreach ($this->userOrganizationReferences as $userRef) {
            if ($userRef->isManager() && $user->getId() === $userRef->getUser()->getId()) {
                $userRef->setManager(false);
            }
        }
    }

    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    /**
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function addGroup(Group $group): void
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addOrganization($this);
        }
    }

    public function removeGroup(Group $group): void
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
            $group->removeOrganization($this);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getWorkspaces()
    {
        return $this->workspaces;
    }

    public function setVat(?string $vat = null): void
    {
        $this->vat = $vat;
    }

    public function getVat(): ?string
    {
        return $this->vat;
    }

    public function setType(?string $type = null): void
    {
        $this->type = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getUserOrganizationReferences()
    {
        return $this->userOrganizationReferences;
    }

    public function hasUser(User $user): bool
    {
        foreach ($this->userOrganizationReferences as $userRef) {
            if ($user->getId() === $userRef->getUser()->getId()) {
                return true;
            }
        }

        return false;
    }

    public function addUser(User $user, ?bool $manager = false): void
    {
        if ($this->getMaxUsers() > -1) {
            $totalUsers = count($this->userOrganizationReferences);
            if ($totalUsers >= $this->getMaxUsers()) {
                throw new \Exception('The organization user limit has been reached');
            }
        }

        $ref = null;
        foreach ($this->userOrganizationReferences as $userOrgaRef) {
            if ($userOrgaRef->getOrganization() === $this && $userOrgaRef->getUser() === $user) {
                $ref = $userOrgaRef;
            }
        }

        if (empty($ref)) {
            $ref = new UserOrganizationReference();
            $ref->setOrganization($this);
            $ref->setUser($user);
            $this->userOrganizationReferences->add($ref);
        }

        $ref->setManager($manager);
    }

    public function removeUser(User $user): void
    {
        $found = null;
        foreach ($this->userOrganizationReferences as $ref) {
            if ($ref->getUser()->getId() === $user->getId() && !$ref->isMain()) {
                $found = $ref;
            }
        }

        if ($found) {
            $this->userOrganizationReferences->removeElement($found);
        }
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getLeft()
    {
        return $this->lft;
    }

    public function getRight()
    {
        return $this->rgt;
    }

    public function getKeys()
    {
        return $this->keys;
    }

    public function getMaxUsers(): ?int
    {
        return $this->maxUsers;
    }

    public function setMaxUsers(int $maxUsers): void
    {
        $this->maxUsers = $maxUsers;
    }

    public function addWorkspace(Workspace $workspace)
    {
        $workspace->addOrganization($this);
    }

    public function removeWorkspace(Workspace $workspace)
    {
        $workspace->removeOrganization($this);
    }
}

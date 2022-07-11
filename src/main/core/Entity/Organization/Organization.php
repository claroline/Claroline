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
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Location\Location;
use Claroline\CoreBundle\Entity\Model\GroupsTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid as BaseUuid;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\User\OrganizationRepository")
 * @ORM\Table(name="claro__organization")
 * @DoctrineAssert\UniqueEntity("name")
 * @Gedmo\Tree(type="nested")
 */
class Organization
{
    use Code;
    use GroupsTrait;
    use Id;
    use Uuid;

    const TYPE_EXTERNAL = 'external';
    const TYPE_INTERNAL = 'internal';

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
     */
    protected $position;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(nullable=true, type="string")
     * @Assert\Email()
     *
     * @var string
     */
    protected $email;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Location\Location",
     *     cascade={"persist"},
     *     inversedBy="organizations"
     * )
     * @ORM\JoinTable(name="claro__location_organization")
     *
     * @var ArrayCollection
     */
    protected $locations;

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
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Organization\Organization", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     *
     * @var Organization[]|ArrayCollection
     */
    protected $children;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     mappedBy="organizations"
     * )
     * @ORM\JoinTable(name="claro_user_workspace")
     *
     * @var Workspace[]|ArrayCollection
     */
    protected $workspaces;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Group",
     *     mappedBy="organizations"
     * )
     * @ORM\JoinTable(name="claro_group_organization")
     *
     * @var ArrayCollection
     */
    protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\User", mappedBy="administratedOrganizations")
     *
     * @var User[]|ArrayCollection
     *
     * @todo reuse $userOrganizationReferences and add a prop on UserOrganizationReference. This will avoid a multiple join.
     */
    protected $administrators;

    /**
     * @ORM\Column(name="is_default", type="boolean")
     */
    protected $default = false;

    /**
     * @ORM\Column(name="is_public", type="boolean")
     */
    protected $public = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    protected $vat;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $type = self::TYPE_INTERNAL;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Organization\UserOrganizationReference",
     *     mappedBy="organization",
     *     cascade={"persist", "remove"},
     *     orphanRemoval=true
     * )
     * @ORM\JoinColumn(name="organization_id", nullable=false)
     *
     * @var ArrayCollection
     */
    protected $userOrganizationReferences;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\CoreBundle\Entity\Cryptography\CryptographicKey", mappedBy="organization")
     */
    protected $keys;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    protected $maxUsers = -1;

    public function __construct()
    {
        $this->refreshUuid();
        // todo : generate unique from name for a more beautiful code
        $this->code = BaseUuid::uuid4()->toString();

        $this->locations = new ArrayCollection();
        $this->workspaces = new ArrayCollection();
        $this->keys = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->administrators = new ArrayCollection();
        $this->userOrganizationReferences = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get parent.
     *
     * @return Organization
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent.
     *
     * @param Organization $parent
     */
    public function setParent(self $parent = null)
    {
        $this->parent = $parent;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setLocations($locations)
    {
        $this->locations = $locations;
    }

    public function getLocations()
    {
        return $this->locations;
    }

    public function addLocation(Location $location)
    {
        return $this->locations->add($location);
    }

    public function removeLocation(Location $location)
    {
        return $this->locations->removeElement($location);
    }

    /**
     * @deprecated use getManagers()
     */
    public function getAdministrators()
    {
        return $this->getManagers();
    }

    /**
     * @deprecated use addManager()
     */
    public function addAdministrator(User $user)
    {
        $this->addManager($user);
    }

    /**
     * @deprecated use removeAdministrator()
     */
    public function removeAdministrator(User $user)
    {
        $this->removeManager($user);
    }

    public function getManagers()
    {
        return $this->administrators;
    }

    public function hasManager(User $user): bool
    {
        return $this->administrators->contains($user);
    }

    public function addManager(User $user)
    {
        if (!$this->administrators->contains($user)) {
            $this->addUser($user);
            $this->administrators->add($user);
            $user->addAdministratedOrganization($this);
        }
    }

    public function removeManager(User $user)
    {
        if ($this->administrators->contains($user)) {
            $this->administrators->removeElement($user);
            $user->removeAdministratedOrganization($this);
        }
    }

    public function setAdministrators(ArrayCollection $users)
    {
        $this->administrators = $users;
    }

    public function setDefault($default)
    {
        $this->default = $default;
    }

    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @deprecated use isDefault()
     */
    public function getDefault()
    {
        return $this->default;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public)
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

    public function addGroup(Group $group)
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addOrganization($this);
        }
    }

    public function removeGroup(Group $group)
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

    /**
     * @param string $vat
     */
    public function setVat($vat)
    {
        $this->vat = $vat;
    }

    /**
     * @return string
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function getUserOrganizationReferences()
    {
        return $this->userOrganizationReferences;
    }

    public function addUser(User $user)
    {
        if ($this->getMaxUsers() > -1) {
            $totalUsers = count($this->getUserOrganizationReferences());
            if ($totalUsers >= $this->getMaxUsers()) {
                throw new \Exception('The organization user limit has been reached');
            }
        }

        $found = false;

        foreach ($this->userOrganizationReferences as $userOrgaRef) {
            if ($userOrgaRef->getOrganization() === $this && $userOrgaRef->getUser() === $user) {
                $found = true;
            }
        }

        if (!$found) {
            $ref = new UserOrganizationReference();
            $ref->setOrganization($this);
            $ref->setUser($user);
            $this->userOrganizationReferences->add($ref);
        }
    }

    public function removeUser(User $user)
    {
        $found = null;

        foreach ($this->userOrganizationReferences as $ref) {
            if ($ref->getUser()->getId() === $user->getId() && !$ref->isMain()) {
                $found = $ref;
            }
        }

        if ($found && count($user->getOrganizations()) > 0) {
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

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function setMaxUsers($maxUsers)
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

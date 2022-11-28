<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Restriction\Locked;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use RuntimeException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CommunityBundle\Repository\RoleRepository")
 * @ORM\Table(name="claro_role")
 * @ORM\HasLifecycleCallbacks
 */
class Role
{
    use Id;
    use Uuid;
    use Description;
    use Locked;

    // TODO : should be a string for better data readability
    const PLATFORM_ROLE = 1;
    const WS_ROLE = 2;
    const USER_ROLE = 4;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="translation_key")
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $translationKey;

    /**
     * should be unidirectional.
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     mappedBy="roles"
     * )
     *
     * @var ArrayCollection
     */
    private $users;

    /**
     * should be unidirectional.
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\AdminTool",
     *     mappedBy="roles"
     * )
     *
     * @var ArrayCollection|AdminTool[]
     */
    private $adminTools;

    /**
     * should be unidirectional.
     *
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Group",
     *     mappedBy="roles"
     * )
     *
     * @var ArrayCollection
     */
    private $groups;

    /**
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $type = self::PLATFORM_ROLE;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     inversedBy="roles"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var Workspace
     */
    private $workspace;

    /**
     * should be unidirectional.
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Tool\ToolRights",
     *     mappedBy="role"
     * )
     */
    private $toolRights;

    /**
     * @ORM\Column(name="personal_workspace_creation_enabled", type="boolean")
     *
     * @var bool
     */
    private $personalWorkspaceCreationEnabled = false;

    /**
     * should be unidirectional.
     *
     * @ORM\OneToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Shortcuts",
     *     mappedBy="role",
     *     cascade={"persist", "merge"}
     * )
     *
     * @var Shortcuts[]|ArrayCollection
     */
    private $shortcuts;

    public function __construct()
    {
        $this->refreshUuid();

        $this->users = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->toolRights = new ArrayCollection();
        $this->adminTools = new ArrayCollection();
        $this->shortcuts = new ArrayCollection();
    }

    public function __toString(): string
    {
        $name = $this->workspace ? '['.$this->workspace->getName().'] '.$this->name : $this->name;

        return "[{$this->getId()}]".$name;
    }

    /**
     * Sets the role name. The name must be prefixed by 'ROLE_'. Note that
     * platform-wide roles (as listed in Claroline/CoreBundle/Security/PlatformRoles)
     * cannot be modified by this setter.
     *
     * @param string $name
     *
     * @throws \RuntimeException if the name isn't prefixed by 'ROLE_' or if the role is platform-wide
     */
    public function setName($name)
    {
        if (0 !== strpos($name, 'ROLE_')) {
            throw new RuntimeException('Role names must start with "ROLE_"');
        }

        if (PlatformRoles::contains($this->name)) {
            throw new RuntimeException('Platform roles cannot be modified');
        }

        if (PlatformRoles::contains($name)) {
            $this->locked = true;
        }

        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setTranslationKey(string $key): void
    {
        $this->translationKey = $key;
    }

    public function getTranslationKey(): string
    {
        return $this->translationKey;
    }

    /**
     * @deprecated use isLocked
     */
    public function isReadOnly()
    {
        return $this->isLocked();
    }

    /**
     * Alias of getName().
     *
     * @return string The role name
     */
    public function getRole()
    {
        return $this->getName();
    }

    /**
     * @ORM\PreRemove
     */
    public function preRemove()
    {
        if (PlatformRoles::contains($this->name)) {
            throw new RuntimeException('Platform roles cannot be deleted');
        }
    }

    /**
     * @deprecated use setLocked
     */
    public function setReadOnly($value)
    {
        $this->setLocked($value);
    }

    /**
     * Get the users property.
     *
     * @return ArrayCollection|User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function addUser(User $user)
    {
        $this->users->add($user);

        if (!$user->hasRole($this)) {
            $user->addRole($this);
        }
    }

    public function addGroup(Group $group)
    {
        $this->groups->add($group);

        if (!$group->hasRole($this)) {
            $group->addRole($this);
        }
    }

    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
        $user->removeRole($this);
    }

    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);
        $group->removeRole($this);
    }

    /**
     * @return ArrayCollection|Group[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setWorkspace(Workspace $ws = null)
    {
        $this->workspace = $ws;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function addToolRights(ToolRights $tr)
    {
        $this->toolRights->add($tr);
    }

    public function getToolRights()
    {
        return $this->toolRights;
    }

    public function getPersonalWorkspaceCreationEnabled()
    {
        return $this->personalWorkspaceCreationEnabled;
    }

    public function isPersonalWorkspaceCreationEnabled()
    {
        return $this->personalWorkspaceCreationEnabled;
    }

    public function setPersonalWorkspaceCreationEnabled($boolean)
    {
        $this->personalWorkspaceCreationEnabled = $boolean;
    }

    /**
     * Get the adminTools property.
     *
     * @return ArrayCollection
     */
    public function getAdminTools()
    {
        return $this->adminTools;
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

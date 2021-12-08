<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_team")
 * @ORM\Entity(repositoryClass="Claroline\TeamBundle\Repository\TeamRepository")
 */
class Team
{
    use Id;
    use Uuid;

    /**
     * @ORM\Column()
     *
     * @var string
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var Workspace
     */
    protected $workspace;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role", cascade={"remove"}
     * )
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @var Role
     */
    protected $role;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinTable(name="claro_team_users")
     *
     * @var ArrayCollection|User[]
     */
    protected $users;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="team_manager", nullable=true, onDelete="SET NULL")
     *
     * @var User
     */
    protected $teamManager;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role", cascade={"remove"}
     * )
     * @ORM\JoinColumn(name="team_manager_role", nullable=true, onDelete="SET NULL")
     *
     * @var Role
     */
    protected $teamManagerRole;

    /**
     * @ORM\Column(name="max_users", type="integer", nullable=true)
     *
     * @var int
     */
    protected $maxUsers;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     *
     * @var bool
     */
    protected $selfRegistration = false;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     *
     * @var bool
     */
    protected $selfUnregistration = false;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode"
     * )
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @var ResourceNode
     */
    protected $directory;

    /**
     * @ORM\Column(name="is_public", type="boolean")
     *
     * @var bool
     */
    protected $isPublic = false;

    /**
     * @ORM\Column(name="dir_deletable", type="boolean", options={"default" = 0})
     *
     * @var bool
     */
    protected $dirDeletable = false;

    /**
     * Team constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();

        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    public function setRole(Role $role = null)
    {
        $this->role = $role;
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Adds an user to team.
     *
     * @return Team
     */
    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    /**
     * Removes an user to team.
     *
     * @return Team
     */
    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    /**
     * @return User
     */
    public function getTeamManager()
    {
        return $this->teamManager;
    }

    public function setTeamManager(User $teamManager = null)
    {
        $this->teamManager = $teamManager;
    }

    /**
     * @return Role
     */
    public function getTeamManagerRole()
    {
        return $this->teamManagerRole;
    }

    public function setTeamManagerRole(Role $teamManagerRole = null)
    {
        $this->teamManagerRole = $teamManagerRole;
    }

    /**
     * @return int
     */
    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    /**
     * @param int $maxUsers
     */
    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    /**
     * @return bool
     */
    public function isSelfRegistration()
    {
        return $this->selfRegistration;
    }

    /**
     * @param bool $selfRegistration
     */
    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    /**
     * @return bool
     */
    public function isSelfUnregistration()
    {
        return $this->selfUnregistration;
    }

    /**
     * @param bool $selfUnregistration
     */
    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    public function getDirectory(): ?ResourceNode
    {
        return $this->directory;
    }

    public function setDirectory(ResourceNode $directory = null)
    {
        $this->directory = $directory;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * @return bool
     */
    public function isDirDeletable()
    {
        return $this->dirDeletable;
    }

    /**
     * @param bool $dirDeletable
     */
    public function setDirDeletable($dirDeletable)
    {
        $this->dirDeletable = $dirDeletable;
    }
}

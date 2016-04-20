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

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="claro_team")
 * @ORM\Entity(repositoryClass="Claroline\TeamBundle\Repository\TeamRepository")
 */
class Team
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     */
    protected $workspace;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $role;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinTable(name="claro_team_users")
     */
    protected $users;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="team_manager", nullable=true, onDelete="SET NULL")
     */
    protected $teamManager;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Role"
     * )
     * @ORM\JoinColumn(name="team_manager_role", nullable=true, onDelete="SET NULL")
     */
    protected $teamManagerRole;

    /**
     * @ORM\Column(name="max_users", type="integer", nullable=true)
     */
    protected $maxUsers;

    /**
     * @ORM\Column(name="self_registration", type="boolean")
     */
    protected $selfRegistration;

    /**
     * @ORM\Column(name="self_unregistration", type="boolean")
     */
    protected $selfUnregistration;

    /**
     * @ORM\OneToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Resource\Directory"
     * )
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $directory;

    /**
     * @ORM\Column(name="is_public", type="boolean")
     */
    protected $isPublic;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getUsers()
    {
        return $this->users->toArray();
    }

    public function getUsersArrayCollection()
    {
        return $this->users;
    }

    public function getTeamManager()
    {
        return $this->teamManager;
    }

    public function getTeamManagerRole()
    {
        return $this->teamManagerRole;
    }

    public function getMaxUsers()
    {
        return $this->maxUsers;
    }

    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }

    public function getSelfUnregistration()
    {
        return $this->selfUnregistration;
    }

    public function getDirectory()
    {
        return $this->directory;
    }

    public function getIsPublic()
    {
        return $this->isPublic;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setWorkspace(Workspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function setRole(Role $role)
    {
        $this->role = $role;
    }

    public function setTeamManager(User $teamManager = null)
    {
        $this->teamManager = $teamManager;
    }

    public function setTeamManagerRole(Role $teamManagerRole)
    {
        $this->teamManagerRole = $teamManagerRole;
    }

    public function setMaxUsers($maxUsers)
    {
        $this->maxUsers = $maxUsers;
    }

    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    public function setDirectory(Directory $directory)
    {
        $this->directory = $directory;
    }

    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * Adds an user to team.
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\TeamBundle\Entity\Team
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
     * @param \Claroline\CoreBundle\Entity\User $user
     *
     * @return \Claroline\TeamBundle\Entity\Team
     */
    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }
}

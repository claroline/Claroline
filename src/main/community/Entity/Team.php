<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Claroline\CommunityBundle\Repository\TeamRepository;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Display\Thumbnail;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Meta\Name;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Table(name: 'claro_team')]
#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    use Id;
    use Uuid;
    use Name;
    use Description;
    use Thumbnail;
    use Poster;

    /**
     *
     *
     * @var Workspace
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Workspace::class)]
    private $workspace;

    /**
     *
     *
     * @var Role
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: Role::class, cascade: ['remove'])]
    private $role;

    /**
     *
     *
     * @var ArrayCollection|User[]
     */
    #[ORM\JoinTable(name: 'claro_team_users')]
    #[ORM\ManyToMany(targetEntity: User::class)]
    private $users;

    /**
     *
     *
     * @var Role
     */
    #[ORM\JoinColumn(name: 'manager_role_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: Role::class, cascade: ['remove'])]
    private $managerRole;

    /**
     * @var int
     */
    #[ORM\Column(name: 'max_users', type: Types::INTEGER, nullable: true)]
    private $maxUsers;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'self_registration', type: Types::BOOLEAN)]
    private $selfRegistration = false;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'self_unregistration', type: Types::BOOLEAN)]
    private $selfUnregistration = false;

    /**
     *
     *
     * @var ResourceNode
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: ResourceNode::class)]
    private $directory;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'is_public', type: Types::BOOLEAN)]
    private $isPublic = false;

    /**
     * @var bool
     */
    #[ORM\Column(name: 'dir_deletable', type: Types::BOOLEAN, options: ['default' => 0])]
    private $dirDeletable = false;

    public function __construct()
    {
        $this->refreshUuid();

        $this->users = new ArrayCollection();
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function setWorkspace(Workspace $workspace): void
    {
        $this->workspace = $workspace;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(Role $role = null): void
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

    public function hasUser(User $user): bool
    {
        return $this->users->contains($user);
    }

    public function addUser(User $user): void
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }
    }

    public function removeUser(User $user): void
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
    }

    public function getManagerRole(): ?Role
    {
        return $this->managerRole;
    }

    public function setManagerRole(Role $managerRole = null): void
    {
        $this->managerRole = $managerRole;
    }

    public function getMaxUsers(): ?int
    {
        return $this->maxUsers;
    }

    public function setMaxUsers(int $maxUsers = null): void
    {
        $this->maxUsers = $maxUsers;
    }

    public function isSelfRegistration(): bool
    {
        return $this->selfRegistration;
    }

    public function setSelfRegistration(bool $selfRegistration): void
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function isSelfUnregistration(): bool
    {
        return $this->selfUnregistration;
    }

    public function setSelfUnregistration(bool $selfUnregistration): void
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

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function setPublic(bool $isPublic): void
    {
        $this->isPublic = $isPublic;
    }

    public function isDirDeletable(): bool
    {
        return $this->dirDeletable;
    }

    public function setDirDeletable(bool $dirDeletable): void
    {
        $this->dirDeletable = $dirDeletable;
    }
}

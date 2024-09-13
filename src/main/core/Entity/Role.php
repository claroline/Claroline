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

use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\Description;
use Claroline\AppBundle\Entity\Restriction\Locked;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CommunityBundle\Repository\RoleRepository")
 *
 * @ORM\Table(name="claro_role")
 */
class Role implements CrudEntityInterface
{
    use Id;
    use Uuid;
    use Description;
    use Locked;

    public const PLATFORM = 'platform';
    public const WORKSPACE = 'workspace';
    public const USER = 'user';

    /**
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank()
     */
    private ?string $name = null;

    /**
     * @ORM\Column(name="translation_key")
     *
     * @Assert\NotBlank()
     */
    private ?string $translationKey = null;

    /**
     * @ORM\ManyToMany(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     mappedBy="roles",
     *     fetch="EXTRA_LAZY"
     * )
     *
     * @deprecated should be unidirectional.
     */
    private Collection $users;

    /**
     * @ORM\Column(name="entity_type", type="string", length="10")
     */
    private string $type = self::PLATFORM;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     inversedBy="roles"
     * )
     *
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private ?Workspace $workspace = null;

    /**
     * @ORM\Column(name="personal_workspace_creation_enabled", type="boolean")
     */
    private bool $personalWorkspaceCreationEnabled = false;

    public function __construct()
    {
        $this->refreshUuid();

        $this->users = new ArrayCollection();
    }

    public static function getIdentifiers(): array
    {
        return ['name'];
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * Sets the role name. The name must be prefixed by 'ROLE_'. Note that
     * platform-wide roles (as listed in Claroline/CoreBundle/Security/PlatformRoles)
     * cannot be modified by this setter.
     *
     * @throws \RuntimeException if the name isn't prefixed by 'ROLE_' or if the role is platform-wide
     */
    public function setName(string $name): void
    {
        if (!str_starts_with($name, 'ROLE_')) {
            throw new \RuntimeException('Role names must start with "ROLE_"');
        }

        if ($this->name && PlatformRoles::contains($this->name)) {
            throw new \RuntimeException('Platform roles cannot be modified');
        }

        if (PlatformRoles::contains($name)) {
            $this->locked = true;
        }

        $this->name = $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
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
     * @deprecated no replacement
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @deprecated no replacement
     */
    public function addUser(User $user): void
    {
        $this->users->add($user);

        if (!$user->hasRole($this)) {
            $user->addRole($this);
        }
    }

    /**
     * @deprecated no replacement
     */
    public function removeUser(User $user): void
    {
        $this->users->removeElement($user);
        $user->removeRole($this);
    }

    /**
     * @deprecated no replacement
     */
    public function addGroup(Group $group): void
    {
        $group->addRole($this);
    }

    /**
     * @deprecated no replacement
     */
    public function removeGroup(Group $group): void
    {
        $group->removeRole($this);
    }

    public function setWorkspace(Workspace $ws = null): void
    {
        $this->workspace = $ws;
    }

    public function getWorkspace(): ?Workspace
    {
        return $this->workspace;
    }

    public function isPersonalWorkspaceCreationEnabled(): bool
    {
        return $this->personalWorkspaceCreationEnabled;
    }

    public function setPersonalWorkspaceCreationEnabled(bool $boolean): void
    {
        $this->personalWorkspaceCreationEnabled = $boolean;
    }
}

<?php

namespace Claroline\CoreBundle\Entity\ConnectionMessage;

use Claroline\AppBundle\Entity\Display\Hidden;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Restriction\AccessibleFrom;
use Claroline\AppBundle\Entity\Restriction\AccessibleUntil;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\ConnectionMessage\ConnectionMessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_connection_message')]
#[ORM\Entity(repositoryClass: ConnectionMessageRepository::class)]
class ConnectionMessage
{
    // identifiers
    use Id;
    use Uuid;
    // restrictions
    use Hidden;
    use AccessibleFrom;
    use AccessibleUntil;

    public const TYPE_ONCE = 'once';
    public const TYPE_ALWAYS = 'always';
    public const TYPE_DISCARD = 'discard';

    #[ORM\Column]
    private ?string $title = null;

    #[ORM\Column(name: 'message_type')]
    private string $type = self::TYPE_ONCE;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $locked = false;

    /**
     * @var Collection<int, Slide>
     */
    #[ORM\OneToMany(targetEntity: Slide::class, mappedBy: 'message', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['order' => 'ASC'])]
    private Collection $slides;

    /**
     * List of roles the message is destined to.
     *
     * @var Collection<int, Role>
     */
    #[ORM\JoinTable(name: 'claro_connection_message_role')]
    #[ORM\ManyToMany(targetEntity: Role::class)]
    private Collection $roles;

    /**
     * List of users for whom the message doesn't have to be displayed anymore.
     *
     * @var Collection<int, User>
     */
    #[ORM\JoinTable(name: 'claro_connection_message_user')]
    #[ORM\ManyToMany(targetEntity: User::class, fetch: 'EXTRA_LAZY')]
    private Collection $users;

    public function __construct()
    {
        $this->refreshUuid();

        $this->slides = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): void
    {
        $this->locked = $locked;
    }

    public function getSlides(): Collection
    {
        return $this->slides;
    }

    public function addSlide(Slide $slide): void
    {
        if (!$this->slides->contains($slide)) {
            $this->slides->add($slide);
            $slide->setMessage($this);
        }
    }

    public function removeSlide(Slide $slide): void
    {
        if ($this->slides->contains($slide)) {
            $this->slides->removeElement($slide);
            $slide->setMessage(null);
        }
    }

    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function removeRole(Role $role): void
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    public function emptyRoles(): void
    {
        $this->roles->clear();
    }

    public function getUsers(): Collection
    {
        return $this->users;
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
}

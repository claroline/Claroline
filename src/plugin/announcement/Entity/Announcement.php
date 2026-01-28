<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Entity;

use Claroline\AnnouncementBundle\Finder\AnnouncementType;
use Claroline\AppBundle\API\Attribute\CrudEntity;
use Claroline\AppBundle\Entity\CrudEntityInterface;
use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\CreatedAt;
use Claroline\AppBundle\Entity\Meta\Creator;
use Claroline\AppBundle\Entity\Meta\UpdatedAt;
use Claroline\AppBundle\Entity\Meta\Views;
use Claroline\AppBundle\Entity\UserViewCounterInterface;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Model\HasWorkspace;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_announcement')]
#[ORM\Entity]
#[CrudEntity(finderClass: AnnouncementType::class)]
class Announcement implements CrudEntityInterface, UserViewCounterInterface
{
    use Id;
    use Uuid;
    use Poster;
    use Creator;
    use CreatedAt;
    use UpdatedAt;
    use HasWorkspace;
    use Views;

    #[ORM\Column(nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    /**
     * @deprecated
     */
    #[ORM\Column(nullable: true)]
    private ?string $announcer = null;

    #[ORM\Column(name: 'publication_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publicationDate = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private ?bool $visible = true;

    #[ORM\Column(name: 'visible_from', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $visibleFrom = null;

    #[ORM\Column(name: 'visible_until', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $visibleUntil = null;

    #[ORM\JoinColumn(name: 'task_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: ScheduledTask::class)]
    private ?ScheduledTask $task = null;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\JoinTable(name: 'claro_announcement_roles')]
    #[ORM\ManyToMany(targetEntity: Role::class)]
    private Collection $roles;

    public function __construct()
    {
        $this->refreshUuid();

        $this->roles = new ArrayCollection();
    }

    public static function getIdentifiers(): array
    {
        return [];
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?\DateTimeInterface $publicationDate = null): void
    {
        $this->publicationDate = $publicationDate;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    public function getVisibleFrom(): ?\DateTimeInterface
    {
        return $this->visibleFrom;
    }

    public function setVisibleFrom(?\DateTimeInterface $visibleFrom = null): void
    {
        $this->visibleFrom = $visibleFrom;
    }

    public function getVisibleUntil(): ?\DateTimeInterface
    {
        return $this->visibleUntil;
    }

    public function setVisibleUntil(?\DateTimeInterface $visibleUntil = null): void
    {
        $this->visibleUntil = $visibleUntil;
    }

    public function getTask(): ?ScheduledTask
    {
        return $this->task;
    }

    public function setTask(?ScheduledTask $task = null): void
    {
        $this->task = $task;
    }

    public function getRoles(): array
    {
        return $this->roles->toArray();
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

    /**
     * @deprecated
     */
    public function getAnnouncer(): ?string
    {
        return $this->announcer;
    }
}

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

use Claroline\AppBundle\Entity\Display\Poster;
use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_announcement')]
#[ORM\Entity]
class Announcement
{
    use Id;
    use Uuid;
    use Poster;

    #[ORM\Column(nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(nullable: true)]
    private ?string $announcer = null;

    #[ORM\Column(name: 'creation_date', type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $creationDate;

    #[ORM\Column(name: 'publication_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publicationDate;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false)]
    private ?bool $visible = true;

    #[ORM\Column(name: 'visible_from', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $visibleFrom = null;

    #[ORM\Column(name: 'visible_until', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $visibleUntil = null;

    #[ORM\JoinColumn(name: 'creator_id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    private ?User $creator = null;

    #[ORM\ManyToOne(targetEntity: AnnouncementAggregate::class, inversedBy: 'announcements')]
    #[ORM\JoinColumn(name: 'aggregate_id', nullable: false, onDelete: 'CASCADE')]
    private ?AnnouncementAggregate $aggregate = null;

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
        $this->creationDate = new \DateTime();
        $this->roles = new ArrayCollection();
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

    public function getAnnouncer(): ?string
    {
        return $this->announcer;
    }

    public function setAnnouncer(?string $announcer): void
    {
        $this->announcer = $announcer;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): void
    {
        $this->creationDate = $creationDate;
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

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator = null): void
    {
        $this->creator = $creator;
    }

    public function getAggregate(): ?AnnouncementAggregate
    {
        return $this->aggregate;
    }

    public function setAggregate(AnnouncementAggregate $aggregate = null): void
    {
        $this->aggregate = $aggregate;
    }

    /**
     * @return ScheduledTask
     */
    public function getTask()
    {
        return $this->task;
    }

    public function setTask(ScheduledTask $task = null)
    {
        $this->task = $task;
    }

    public function getRoles()
    {
        return $this->roles->toArray();
    }

    public function addRole(Role $role)
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function removeRole(Role $role)
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    public function emptyRoles()
    {
        $this->roles->clear();
    }
}

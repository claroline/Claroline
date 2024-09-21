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
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'claro_announcement')]
#[ORM\Entity]
class Announcement
{
    use Id;
    use Uuid;
    use Poster;

    /**
     * The title of the Announcement.
     *
     *
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $title;

    /**
     * The content of the Announcement.
     *
     * @var string
     */
    #[ORM\Column(type: 'text')]
    private $content;

    /**
     * @var string
     */
    #[ORM\Column(nullable: true)]
    private $announcer;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'creation_date', type: 'datetime', nullable: false)]
    private $creationDate;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'publication_date', type: 'datetime', nullable: true)]
    private $publicationDate;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', nullable: false)]
    private $visible;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'visible_from', type: 'datetime', nullable: true)]
    private $visibleFrom;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'visible_until', type: 'datetime', nullable: true)]
    private $visibleUntil;

    /**
     *
     * @var User
     */
    #[ORM\JoinColumn(name: 'creator_id', onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Claroline\CoreBundle\Entity\User::class)]
    private $creator;

    #[ORM\JoinColumn(name: 'aggregate_id', onDelete: 'CASCADE', nullable: false)]
    #[ORM\ManyToOne(targetEntity: \Claroline\AnnouncementBundle\Entity\AnnouncementAggregate::class, inversedBy: 'announcements')]
    private ?AnnouncementAggregate $aggregate = null;

    #[ORM\JoinColumn(name: 'task_id', nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: \Claroline\SchedulerBundle\Entity\ScheduledTask::class)]
    private $task;

    #[ORM\JoinTable(name: 'claro_announcement_roles')]
    #[ORM\ManyToMany(targetEntity: \Claroline\CoreBundle\Entity\Role::class)]
    private Collection $roles;

    public function __construct()
    {
        $this->refreshUuid();
        $this->creationDate = new \DateTime();
        $this->roles = new ArrayCollection();
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content.
     *
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Get announcer.
     *
     * @return string
     */
    public function getAnnouncer()
    {
        return $this->announcer;
    }

    /**
     * Set announcer.
     *
     * @param string $announcer
     */
    public function setAnnouncer($announcer)
    {
        $this->announcer = $announcer;
    }

    /**
     * Get creation date.
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set creation date.
     */
    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * Get publication date.
     *
     * @return \DateTime
     */
    public function getPublicationDate()
    {
        return $this->publicationDate;
    }

    /**
     * Set publication date.
     *
     * @param \DateTime $publicationDate
     */
    public function setPublicationDate(\DateTime $publicationDate = null)
    {
        $this->publicationDate = $publicationDate;
    }

    /**
     * Is visible ?
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * Set visible.
     *
     * @param bool $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    /**
     * Get visible from.
     *
     * @return \DateTime
     */
    public function getVisibleFrom()
    {
        return $this->visibleFrom;
    }

    /**
     * Set visible from.
     *
     * @param \DateTime $visibleFrom
     */
    public function setVisibleFrom(\DateTime $visibleFrom = null)
    {
        $this->visibleFrom = $visibleFrom;
    }

    /**
     * Get visible until.
     *
     * @return \DateTime
     */
    public function getVisibleUntil()
    {
        return $this->visibleUntil;
    }

    /**
     * Set visible until.
     *
     * @param \DateTime $visibleUntil
     */
    public function setVisibleUntil(\DateTime $visibleUntil = null)
    {
        $this->visibleUntil = $visibleUntil;
    }

    /**
     * Get creator.
     *
     * @return User
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set creator.
     */
    public function setCreator(?User $creator = null)
    {
        $this->creator = $creator;
    }

    /**
     * Get parent aggregate.
     *
     * @return AnnouncementAggregate
     */
    public function getAggregate()
    {
        return $this->aggregate;
    }

    /**
     * Set parent aggregate.
     *
     * @param AnnouncementAggregate $aggregate
     */
    public function setAggregate(AnnouncementAggregate $aggregate = null)
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

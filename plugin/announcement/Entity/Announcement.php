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

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Claroline\AnnouncementBundle\Repository\AnnouncementRepository")
 * @ORM\Table(name="claro_announcement")
 */
class Announcement
{
    /**
     * The unique identifier of the Announcement.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    use UuidTrait;

    /**
     * The title of the Announcement.
     *
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $title;

    /**
     * The content of the Announcement.
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     *
     * @var string
     */
    private $content;

    /**
     * @ORM\Column(nullable=true)
     *
     * @var string
     */
    private $announcer;

    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     *
     * @var \DateTime
     */
    private $creationDate;

    /**
     * @ORM\Column(name="publication_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $publicationDate;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     *
     * @var bool
     */
    private $visible;

    /**
     * @ORM\Column(name="visible_from", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $visibleFrom;

    /**
     * @ORM\Column(name="visible_until", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    private $visibleUntil;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="creator_id", onDelete="CASCADE", nullable=false)
     *
     * @var User
     */
    private $creator;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\AnnouncementBundle\Entity\AnnouncementAggregate",
     *     inversedBy="announcements"
     * )
     * @ORM\JoinColumn(name="aggregate_id", onDelete="CASCADE", nullable=false)
     *
     * @var AnnouncementAggregate
     */
    private $aggregate;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Task\ScheduledTask"
     * )
     * @ORM\JoinColumn(name="task_id", nullable=true)
     */
    private $task;

    /**
     * @ORM\ManyToMany(targetEntity="Claroline\CoreBundle\Entity\Role")
     * @ORM\JoinTable(name="claro_announcement_roles")
     */
    private $roles;

    public function __construct()
    {
        $this->refreshUuid();
        $this->creationDate = new \DateTime();
        $this->roles = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     *
     * @param \DateTime $creationDate
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
     *
     * @param User $creator
     */
    public function setCreator(User $creator)
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
    public function setAggregate(AnnouncementAggregate $aggregate)
    {
        $this->aggregate = $aggregate;
    }

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

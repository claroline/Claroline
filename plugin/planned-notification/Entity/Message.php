<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\PlannedNotificationBundle\Entity;

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_plannednotificationbundle_message")
 */
class Message
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace"
     * )
     * @ORM\JoinColumn(name="workspace_id", nullable=false, onDelete="CASCADE")
     *
     * @var Workspace
     */
    protected $workspace;

    /**
     * @ORM\Column(name="title", nullable=false)
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(name="content", type="text", nullable=false)
     *
     * @var string
     */
    protected $content;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\PlannedNotificationBundle\Entity\PlannedNotification",
     *     mappedBy="message",
     *     cascade={"persist"}
     * )
     *
     * @var PlannedNotification
     */
    protected $notifications;

    /**
     * Message constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
        $this->notifications = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Workspace
     */
    public function getWorkspace()
    {
        return $this->workspace;
    }

    /**
     * @param Workspace $workspace
     */
    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return array
     */
    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param PlannedNotification $notification
     */
    public function addNotification(PlannedNotification $notification)
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
        }
    }

    /**
     * @param PlannedNotification $notification
     */
    public function removeNotification(PlannedNotification $notification)
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
        }
    }

    public function emptyNotifications()
    {
        $this->notifications->clear();
    }
}

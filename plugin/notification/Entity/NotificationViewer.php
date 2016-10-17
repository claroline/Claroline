<?php

namespace Icap\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Index;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="Icap\NotificationBundle\Repository\NotificationViewerRepository")
 * @ORM\Table(
 *     name="icap__notification_viewer",
 *     indexes={@Index(name="viewer_idx", columns={"viewer_id"})}
 * )
 */
class NotificationViewer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Groups({"api_notification"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\NotificationBundle\Entity\Notification")
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @JMS\Groups({"api_notification"})
     */
    protected $notification;

    /**
     * @ORM\Column(type="integer", name="viewer_id")
     */
    protected $viewerId;

    /**
     * @ORM\Column(type="boolean", name="status", nullable=true)
     * @JMS\Groups({"api_notification"})
     */
    protected $status;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set notification.
     *
     * @param \Icap\NotificationBundle\Entity\Notification $notification
     *
     * @return NotificationViewer
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification.
     *
     * @return \Icap\NotificationBundle\Entity\Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set viewerId.
     *
     * @param int $viewerId
     *
     * @return NotificationViewer
     */
    public function setViewerId($viewerId)
    {
        $this->viewerId = $viewerId;

        return $this;
    }

    /**
     * Get viewerId.
     *
     * @return int
     */
    public function getViewerId()
    {
        return $this->viewerId;
    }

    /**
     * Set status.
     *
     * @param bool $status
     *
     * @return NotificationViewer
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return bool
     */
    public function getStatus()
    {
        return $this->status;
    }
}

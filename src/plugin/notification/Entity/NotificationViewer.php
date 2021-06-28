<?php

namespace Icap\NotificationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Icap\NotificationBundle\Repository\NotificationViewerRepository")
 * @ORM\Table(
 *     name="icap__notification_viewer",
 *     indexes={@ORM\Index(name="viewer_idx", columns={"viewer_id"})}
 * )
 */
class NotificationViewer
{
    use Id;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\NotificationBundle\Entity\Notification")
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $notification;

    /**
     * @ORM\Column(type="integer", name="viewer_id")
     */
    private $viewerId;

    /**
     * @ORM\Column(type="boolean", name="status", nullable=true)
     */
    private $status;

    /**
     * Set notification.
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
     * @return Notification
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

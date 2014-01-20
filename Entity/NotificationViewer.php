<?php

namespace Icap\NotificationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Icap\NotificationBundle\Repository\NotificationViewerRepository")
 * @ORM\Table(name="icap__notification_viewer")
 */
class NotificationViewer
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\NotificationBundle\Entity\Notification")
     * @ORM\JoinColumn(name="notification_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $notification;

    /**
     * @ORM\Column(type="integer", name="viewer_id")
     */
    protected $viewerId;

    /**
     * @ORM\Column(type="boolean", name="status", nullable=true)
     */
    protected $status;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set notification
     *
     * @param \Icap\NotificationBundle\Entity\Notification $notification
     * @return NotificationViewer
     */
    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return \Icap\NotificationBundle\Entity\Notification
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set viewerId
     *
     * @param integer $viewerId
     * @return NotificationViewer
     */
    public function setViewerId($viewerId)
    {
        $this->viewerId = $viewerId;

        return $this;
    }

    /**
     * Get viewerId
     *
     * @return integer
     */
    public function getViewerId()
    {
        return $this->viewerId;
    }

    /**
     * Set status
     *
     * @param boolean $status
     * @return NotificationViewer
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

}
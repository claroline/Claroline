<?php

namespace Claroline\CoreBundle\Entity;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\EventRepository")
 * @ORM\Table(name="claro_event")
 */
class Event
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="title", type="string" , length=50)
     */
    private $title;

    /**
     * @ORM\Column(name="start", type="integer")
     */
    private $start;

    /**
     * @ORM\Column(name="end", type="integer" , nullable=true)
     */
    private $end;

    /**
     * @ORM\Column(name="description", type="string" , nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace",
     *     inversedBy="events", cascade={"persist"}
     * )
     * @ORM\JoinColumn(name="workspace_id", referencedColumnName="id")
     */
    private $workspace;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\Column(name="allday", type="boolean" , nullable=true)
     */
    private $allDay = true;

     /**
     * @ORM\Column(name="color", type="string" , nullable=true)
     */
    private $color;

     /**
     * @ORM\Column(name="priority", type="string" , nullable=true)
     */
    private $priority;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getStart()
    {

        return $this->start;
    }

    public function setStart(\DateTime $start)
    {
        $timestamp = $start->getTimestamp();
        $this->start = $timestamp;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd(\DateTime $end)
    {
        $this->end = $end->getTimestamp();
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getWorkspaceId()
    {
        return $this->workspaceId;
    }

    public function setWorkspaceId($workspaceId)
    {
        $this->workspaceId = $workspaceId;
    }

    public function getWorkspace()
    {
        return $this->workspace;
    }

    public function setWorkspace(AbstractWorkspace $workspace)
    {
        $this->workspace = $workspace;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getAllDay()
    {
        return $this->allDay;
    }

    public function setAllDay($allDay)
    {
        $this->allDay = (bool) $allDay;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority( $priority)
    {
        $this->priority = $priority;
    }

}
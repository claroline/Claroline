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
     * @ORM\Column(name="start_date", type="integer",nullable=true)
     */
    private $start;

    /**
     * @ORM\Column(name="end_date", type="integer" , nullable=true)
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
    private $allDay;

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
        if (is_null($this->start)) {

            return $this->start;

        } else {
            $date = date('d-m-Y', $this->start);

            return (new \Datetime($date));

        }

    }

    public function setStart($start)
    {
        if (!is_null($start)) {
            if ($start instanceof \Datetime) {
                $this->start = $start->getTimestamp();
            } else {
                $date  = new \Datetime($start);
                $this->start = $date->getTimestamp();
            }
        }
    }

    public function getEnd()
    {
        if (is_null($this->end)) {

            return $this->end;

        } else {
            $date = date('d-m-Y', $this->end);

            return (new \Datetime($date));
        }

    }

    public function setEnd($end)
    {
        if (!is_null($end)) {
            if ($end instanceof \Datetime) {
                $this->end = $end-> getTimestamp();
            } else {
                $date  = new \Datetime($end);
                $this->end = $date->getTimestamp();
            }
        }
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
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

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority( $priority)
    {
        $this->priority = $priority;
    }

}
<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Entity;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Claroline\AgendaBundle\Validator\Constraints\DateRange;

/**
 * @ORM\Entity(repositoryClass="Claroline\AgendaBundle\Repository\EventRepository")
 * @ORM\Table(name="claro_event")
 * @DateRange()
 */
class Event implements \JsonSerializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(length=50)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(name="start_date", type="integer", nullable=true)
     */
    private $start;

    /**
     * @ORM\Column(name="end_date", type="integer", nullable=true)
     */
    private $end;

    /**
     * @ORM\Column(nullable=true, type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Workspace\Workspace",
     *     cascade={"persist"}
     * )
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $workspace;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\Column(name="is_all_day", type="boolean")
     */
    private $allDay = false;

    /**
     * @ORM\Column(name="is_task", type="boolean")
     */
    private $isTask = false;

    /**
     * @ORM\Column(name="is_task_done", type="boolean")
     */
    private $isTaskDone = false;

    /**
     * @ORM\ManyToMany(
     *      targetEntity="Claroline\AgendaBundle\Entity\EventCategory",
     *      inversedBy="events"
     * )
     * @ORM\JoinTable(name="claro_event_event_category")
     */
    private $eventCategories;

     /**
     * @ORM\Column(nullable=true)
     */
    private $priority;

    //If this parameter is set to false, NOBODY could modify the event. This parameter is useful for displaying an event of another bundle without possibility of modification.
    /**
     * @ORM\Column(name="is_editable", nullable=true, type="boolean")
     */
    private $isEditable;

    private $dateRange;

    public function __construct()
    {
        $this->eventCategories = new ArrayCollection();
    }

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

    //Returns a String for the DateTimePicker of the AgendaType
    public function getStart()
    {
        return date('d/m/Y H:i', $this->start);
    }

    public function getStartInTimestamp()
    {
        return $this->start;
    }

    public function getStartInDateTime()
    {
        return \Datetime::createFromFormat('U', $this->start);
    }

    public function setStart($start)
    {
        if (is_string($start)) {
            $dateFormat = $this->isAllDay() ? 'd/m/Y' : 'd/m/Y H:i';
            $dateTime = \DateTime::createFromFormat($dateFormat, $start);
            if (!$dateTime) {
                $this->start = null;
            } else {
                $this->start = $dateTime->getTimestamp();
            }
        } else {
            $this->start = $start;
        }

        return $this;
    }

    //Returns a String for the DateTimePicker of the AgendaType
    public function getEnd()
    {
        return date('d/m/Y H:i', $this->end);
    }

    public function getEndInTimestamp()
    {
        return $this->end;
    }

    public function getEndInDateTime()
    {
        return \Datetime::createFromFormat('U', $this->end);
    }

    public function setEnd($end)
    {
        if (is_string($end)) {
            $dateFormat = $this->isAllDay() ? 'd/m/Y' : 'd/m/Y H:i';
            $dateTime = \DateTime::createFromFormat($dateFormat, $end);
            if (!$dateTime) {
                $this->end = null;
            } else {
                $this->end = $dateTime->getTimestamp();
            }
        } else {
            $this->end = $end;
        }

        return $this;
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

    public function setWorkspace($workspace = null)
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

    public function isAllDay()
    {
        return $this->allDay;
    }

    public function setIsAllDay($isAllDay)
    {
        $this->allDay = (bool) $isAllDay;
    }

    /**
     * @param EventCategory $category
     */
    public function addEventCategory(EventCategory $category)
    {
        $this->eventCategories->add($category);
    }

    /**
     * @return ArrayCollection
     */
    public function getEventCategories()
    {
        return $this->eventCategories;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function setDateRange($dateRange)
    {
        $this->dateRange = $dateRange;
    }

    public function getDateRange()
    {
        return $this->dateRange;
    }

    public function setIsTask($isTask)
    {
        $this->isTask = $isTask;
    }

    public function getIsTask()
    {
        return $this->isTask;
    }

    public function isTask()
    {
        return $this->isTask;
    }

    public function setIsTaskDone($isTaskDone)
    {
        $this->isTaskDone = $isTaskDone;

        return $this;
    }

    public function isTaskDone()
    {
        return $this->isTaskDone;
    }

    public function setIsEditable($isEditable)
    {
        $this->isEditable = $isEditable;

        return $this;
    }

    public function isEditable()
    {
        return $this->isEditable;
    }

    public function jsonSerialize()
    {
        $start = is_null($this->getStart()) ? null : $this->getStartInTimestamp();
        $end = is_null($this->getEnd()) ? null : $this->getEndInTimestamp();
        $startDate = new \DateTime();
        $startDate->setTimeStamp($start);
        $startIso = $startDate->format(\DateTime::ISO8601);
        $endDate = new \DateTime();
        $startDate->setTimeStamp($end);
        $endIso = $startDate->format(\DateTime::ISO8601);

        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'start' => $startIso,
            'end' => $endIso,
            'color' => $this->getPriority(),
            'allDay' => $this->isAllDay(),
            'isTask' => $this->isTask(),
            'isTaskDone' => $this->isTaskDone(),
            'owner' => $this->getUser()->getUsername(),
            'description' => $this->getDescription(),
            'workspace_id' => $this->getWorkspace() ? $this->getWorkspace()->getId(): null,
            'workspace_name' => $this->getWorkspace() ? $this->getWorkspace()->getName(): null,
            'className' => 'event_' . $this->getId(),
            'isEditable' => $this->isEditable(),
            'durationEditable' => !$this->isTask() && $this->isEditable() !== false // If it's a task, disable resizing
        ];
    }
}

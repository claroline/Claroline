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

    private $recurring;
    //public because of the symfony2 form does't use the appropriate setter and we need that value.
    //@see AgendaManager::setEventDate
    public $startHours;
    public $endHours;

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

    public function getStart()
    {
        if (is_null($this->start)) {
            return null;
        } else {
            $date = date('d-m-Y H:i', $this->start);

            return (new \Datetime($date));
        }
    }

    public function setStart($start)
    {
        if (!is_null($start)) {
            if ($start instanceof \Datetime) {
                $this->start = $start->getTimestamp();
            } elseif (is_int($start)) {
                $this->start = $start;
            } else {
                throw new \Exception('Not an integer nor date.');
            }
        } else {
            $this->start = null;
        }
    }

    public function getEnd()
    {
        if (is_null($this->end)) {
            return null;

        } else {
            $date = date('d-m-Y H:i', $this->end);

            return (new \Datetime($date));
        }
    }

    public function setEnd($end)
    {
        if (!is_null($end)) {
            if ($end instanceof \Datetime) {
                $this->end = $end->getTimestamp();
            } elseif (is_int($end)) {
                $this->end = $end;
            } else {
                throw new \Exception('Not an integer nor date.');
            }
        } else {
            $this->end = null;
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
    public function getRecurring()
    {
        return $this->recurring;
    }

    public function setRecurring($recurring)
    {
        $this->recurring = $recurring;
    }

    //returns a timestamp for the form
    public function getStartHours()
    {
        //For some reason, symfony2 always substract 3600. Timestamp for hours 0 = -3600 wich is weird.
        //This couldn't be fixed be setting the timezone in the form field.
        return $this->getStart() ?
            (int) $this->getStart()->format('H') * 3600 + (int) $this->getStart()->format('i') * 60 - 3600:
            null;
    }

    public function setStartHours($startHours)
    {
        $this->startHours = $startHours;
    }

    //returns a timestamp for the form
    public function getEndHours()
    {
        //For some reason, symfony2 always substract 3600. Timestamp for hours 0 = -3600 wich is weird.
        //This couldn't be fixed be setting the timezone in the form field.
        return $this->getEnd() ?
            (int) $this->getEnd()->format('H') * 3600 + (int) $this->getEnd()->format('i') * 60 - 3600:
            null;
    }

    public function setEndHours($endHours)
    {
        $this->endHours = $endHours;
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
        $start = is_null($this->getStart()) ? null : $this->getStart()->getTimestamp();
        $end = is_null($this->getEnd()) ? null : $this->getEnd()->getTimestamp();
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
            'endHours' => $this->getEndHours(),
            'startHours' => $this->getStartHours(),
            'className' => 'event_' . $this->getId(),
            'isEditable' => $this->isEditable(),
            'durationEditable' => !$this->isTask() && $this->isEditable() !== false // If it's a task, disable resizing
        ];
    }
}

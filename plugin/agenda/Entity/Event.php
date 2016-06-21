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
class Event
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
     * @ORM\Column(nullable=true)
     */
    private $priority;

    /**
     * @ORM\OneToMany(targetEntity="Claroline\AgendaBundle\Entity\EventInvitation", mappedBy="event")
     * @ORM\JoinColumn(nullable=true)
     */
    private $eventInvitations = null;

    //If this parameter is set to false, NOBODY could modify the event. This parameter is useful for displaying an event of another bundle without possibility of modification.
    /**
     * @ORM\Column(name="is_editable", nullable=true, type="boolean")
     */
    private $isEditable;

    public function __construct()
    {
        $this->eventInvitations = new ArrayCollection();
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
        } elseif (is_int($start)) {
            $this->start = $start;
        } elseif ($start instanceof \DateTime) {
            $this->start = $start->getTimestamp();
        } else {
            throw new \Exception('Date format is not supported');
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
        } elseif (is_int($end)) {
            $this->end = $end;
        } elseif ($end instanceof \DateTime) {
            $this->end = $end->getTimestamp();
        } else {
            throw new \Exception('Date format is not supported');
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

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
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

    /**
     * Set allDay.
     *
     * @param bool $allDay
     *
     * @return Event
     */
    public function setAllDay($allDay)
    {
        $this->allDay = $allDay;

        return $this;
    }

    /**
     * Get allDay.
     *
     * @return bool
     */
    public function getAllDay()
    {
        return $this->allDay;
    }

    /**
     * Get isTaskDone.
     *
     * @return bool
     */
    public function getIsTaskDone()
    {
        return $this->isTaskDone;
    }

    /**
     * Get isEditable.
     *
     * @return bool
     */
    public function getIsEditable()
    {
        return $this->isEditable;
    }

    /**
     * Add eventInvitation.
     *
     * @param \Claroline\AgendaBundle\Entity\EventInvitation $eventInvitation
     *
     * @return Event
     */
    public function addEventInvitation(\Claroline\AgendaBundle\Entity\EventInvitation $eventInvitation)
    {
        $this->eventInvitations[] = $eventInvitation;

        return $this;
    }

    /**
     * Remove eventInvitation.
     *
     * @param \Claroline\AgendaBundle\Entity\EventInvitation $eventInvitation
     */
    public function removeEventInvitation(\Claroline\AgendaBundle\Entity\EventInvitation $eventInvitation)
    {
        $this->eventInvitations->removeElement($eventInvitation);
    }

    /**
     * Get eventInvitations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEventInvitations()
    {
        return $this->eventInvitations;
    }

    public function jsonSerialize(User $user = null)
    {
        $guests = [];
        $invitation = null;
        foreach ($this->getEventInvitations() as $eventInvitation) {
            $guests[] = [
                'user_name' => $eventInvitation->getUser()->getUserName(),
                'status' => $eventInvitation->getStatus(),
            ];

            if ($eventInvitation->getUser() === $user) {
                $invitation = $eventInvitation;
            }
        }

        return [
            'id' => $this->getId(),
            'title' => $invitation && !is_null($invitation->getTitle()) ? $invitation->getTitle() : $this->getTitle(),
            'start' => \Datetime::createFromFormat('U', $this->start)->format(\DateTime::ISO8601),
            'end' => \Datetime::createFromFormat('U', $this->end)->format(\DateTime::ISO8601),
            'color' => $this->getPriority(),
            'allDay' => $this->isAllDay(),
            'isTask' => $this->isTask(),
            'isTaskDone' => $this->isTaskDone(),
            'owner' => $this->getUser()->getUsername(),
            'description' => $invitation && !is_null($invitation->getDescription()) ? $invitation->getDescription() : $this->getDescription(),
            'workspace_id' => $this->getWorkspace() ? $this->getWorkspace()->getId() : null,
            'workspace_name' => $this->getWorkspace() ? $this->getWorkspace()->getName() : null,
            'className' => 'event_'.$this->getId(),
            'isEditable' => $this->isEditable() !== false && !$invitation,
            'durationEditable' => !$this->isTask() && $this->isEditable() !== false && !$invitation, // If it's a task, disable resizing
            'invitations' => $guests,
            'is_guest' => !is_null($invitation),
            'event_invitation_status' => [
                'ignore' => EventInvitation::IGNORE,
                'join' => EventInvitation::JOIN,
                'maybe' => EventInvitation::MAYBE,
                'resign' => EventInvitation::RESIGN,
            ], //We have to passed the status list of the eventInvitation for the popover render because twig.js doesn't have the constant function
        ];
    }
}

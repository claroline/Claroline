<?php

namespace FormaLibre\SupportBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="formalibre_support_intervention")
 * @ORM\Entity
 */
class Intervention
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL", nullable=true)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\SupportBundle\Entity\Ticket",
     *     inversedBy="interventions"
     * )
     * @ORM\JoinColumn(name="ticket_id", onDelete="CASCADE")
     */
    protected $ticket;

    /**
     * @ORM\Column(name="start_date", type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(name="end_date", type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $duration;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\SupportBundle\Entity\Status"
     * )
     * @ORM\JoinColumn(name="status_id", onDelete="SET NULL", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $externalComment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $internalComment;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    public function getTicket()
    {
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getExternalComment()
    {
        return $this->externalComment;
    }

    public function setExternalComment($externalComment)
    {
        $this->externalComment = $externalComment;
    }

    public function getInternalComment()
    {
        return $this->internalComment;
    }

    public function setInternalComment($internalComment)
    {
        $this->internalComment = $internalComment;
    }
}

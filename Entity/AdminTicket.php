<?php

namespace FormaLibre\SupportBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use FormaLibre\SupportBundle\Entity\Ticket;

/**
 * @ORM\Table(name="formalibre_admin_ticket")
 * @ORM\Entity(repositoryClass="FormaLibre\SupportBundle\Repository\TicketRepository")
 */
class AdminTicket
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\SupportBundle\Entity\Ticket"
     * )
     * @ORM\JoinColumn(name="ticket_id", onDelete="CASCADE")
     */
    protected $ticket;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $priority;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $details;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTicket()
    {
        return $this->ticket;
    }

    public function setTicket(Ticket $ticket)
    {
        $this->ticket = $ticket;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }
}

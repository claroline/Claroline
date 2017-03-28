<?php

namespace FormaLibre\SupportBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="formalibre_support_ticket_user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="support_ticket_unique_user", columns={"ticket_id", "user_id"})
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"ticket", "user"})
 */
class TicketUser
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
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\SupportBundle\Entity\Ticket"
     * )
     * @ORM\JoinColumn(name="ticket_id", onDelete="CASCADE")
     */
    protected $ticket;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $active = false;

    /**
     * @ORM\Column(name="activation_date", type="datetime", nullable=true)
     */
    protected $activationDate;

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

    public function setUser(User $user)
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

    public function isActive()
    {
        return $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }

    public function getActivationDate()
    {
        return $this->activationDate;
    }

    public function setActivationDate($activationDate)
    {
        $this->activationDate = $activationDate;
    }
}

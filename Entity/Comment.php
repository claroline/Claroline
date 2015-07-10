<?php

namespace FormaLibre\SupportBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="formalibre_support_comment")
 * @ORM\Entity(repositoryClass="FormaLibre\SupportBundle\Repository\CommentRepository")
 */
class Comment
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
     *     targetEntity="FormaLibre\SupportBundle\Entity\Ticket"
     * )
     * @ORM\JoinColumn(name="ticket_id", onDelete="CASCADE")
     */
    protected $ticket;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="edition_date", type="datetime", nullable=true)
     */
    protected $editionDate;

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

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getEditionDate()
    {
        return $this->editionDate;
    }

    public function setEditionDate(\DateTime $editionDate = null)
    {
        $this->editionDate = $editionDate;
    }
}

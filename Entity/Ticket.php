<?php

namespace FormaLibre\SupportBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="formalibre_support_ticket")
 * @ORM\Entity(repositoryClass="FormaLibre\SupportBundle\Repository\TicketRepository")
 */
class Ticket
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column()
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    protected $description;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(name="contact_mail")
     * @Assert\NotBlank()
     * @Assert\Email(checkMX = false)
     */
    protected $contactMail;

    /**
     * @ORM\Column(name="contact_phone")
     * @Assert\NotBlank()
     */
    protected $contactPhone;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="integer")
     */
    protected $num;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\SupportBundle\Entity\Type"
     * )
     * @ORM\JoinColumn(name="type_id", onDelete="CASCADE")
     */
    protected $type;

    /**
     * @ORM\Column(name="level", type="integer")
     */
    protected $level = 0;

    /**
     * @ORM\OneToMany(
     *     targetEntity="FormaLibre\SupportBundle\Entity\Comment",
     *     mappedBy="ticket"
     * )
     */
    protected $comments;

    /**
     * @ORM\OneToMany(
     *     targetEntity="FormaLibre\SupportBundle\Entity\Intervention",
     *     mappedBy="ticket"
     * )
     */
    protected $interventions;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $details;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->interventions = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getNum()
    {
        return $this->num;
    }

    public function setNum($num)
    {
        $this->num = $num;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType(Type $type)
    {
        $this->type = $type;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function getComments()
    {
        return $this->comments->toArray();
    }

    public function getInterventions()
    {
        return $this->interventions->toArray();
    }

    public function getContactMail()
    {
        return $this->contactMail;
    }

    public function setContactMail($contactMail)
    {
        $this->contactMail = $contactMail;
    }

    public function getContactPhone()
    {
        return $this->contactPhone;
    }

    public function setContactPhone($contactPhone)
    {
        $this->contactPhone = $contactPhone;
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

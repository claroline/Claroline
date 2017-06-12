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
     * @ORM\Column(name="contact_phone", nullable=true)
     */
    protected $contactPhone;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     */
    protected $creationDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
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
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\SupportBundle\Entity\Status"
     * )
     * @ORM\JoinColumn(name="status_id", onDelete="SET NULL", nullable=true)
     */
    protected $status;

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

    /**
     * @ORM\Column(name="user_active", type="boolean", options={"default" = 1})
     */
    protected $userActive = true;

    /**
     * @ORM\Column(name="admin_active", type="boolean", options={"default" = 1})
     */
    protected $adminActive = true;

    /**
     * @ORM\Column(name="forwarded", type="boolean", options={"default" = 0})
     */
    protected $forwarded = false;

    /**
     * @ORM\OneToOne(targetEntity="FormaLibre\SupportBundle\Entity\Ticket")
     * @ORM\JoinColumn(name="linked_ticket_id", nullable=true, onDelete="SET NULL")
     */
    protected $linkedTicket;

    /**
     * @ORM\Column(name="official_uuid", nullable=true)
     */
    protected $officialUuid;

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

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(Status $status = null)
    {
        $this->status = $status;
    }

    public function getComments()
    {
        return $this->comments->toArray();
    }

    public function getPublicComments()
    {
        $comments = $this->comments->toArray();
        $publicComments = [];

        foreach ($comments as $comment) {
            if ($comment->getType() === Comment::PUBLIC_COMMENT) {
                $publicComments[] = $comment;
            }
        }

        return array_reverse($publicComments);
    }

    public function getPrivateComments()
    {
        $comments = $this->comments->toArray();
        $privateComments = [];

        foreach ($comments as $comment) {
            if ($comment->getType() === Comment::PRIVATE_COMMENT) {
                $privateComments[] = $comment;
            }
        }

        return array_reverse($privateComments);
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

    public function isUserActive()
    {
        return $this->userActive;
    }

    public function setUserActive($userActive)
    {
        $this->userActive = $userActive;
    }

    public function isAdminActive()
    {
        return $this->adminActive;
    }

    public function setAdminActive($adminActive)
    {
        $this->adminActive = $adminActive;
    }

    public function isOpen()
    {
        return !is_null($this->details) && isset($this->details['isOpen']) ? $this->details['isOpen'] : false;
    }

    public function setOpen($open)
    {
        if (is_null($this->details)) {
            $this->details = [];
        }
        $this->details['isOpen'] = $open;
    }

    public function isForwarded()
    {
        return $this->forwarded;
    }

    public function setForwarded($forwarded)
    {
        $this->forwarded = $forwarded;
    }

    public function getLinkedTicket()
    {
        return $this->linkedTicket;
    }

    public function setLinkedTicket(Ticket $linkedTicket)
    {
        $this->linkedTicket = $linkedTicket;
    }

    public function getOfficialUuid()
    {
        return $this->officialUuid;
    }

    public function setOfficialUuid($officialUuid)
    {
        $this->officialUuid = $officialUuid;
    }
}

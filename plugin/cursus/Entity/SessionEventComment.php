<?php

namespace Claroline\CursusBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="claro_cursusbundle_session_event_comment")
 * @ORM\Entity
 */
class SessionEventComment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"api_cursus", "api_user_min"})
     */
    protected $content;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE", nullable=false)
     * @Groups({"api_user_min"})
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\SessionEvent",
     *     inversedBy="comments"
     * )
     * @ORM\JoinColumn(name="session_event_id", onDelete="CASCADE", nullable=false)
     */
    protected $sessionEvent;

    /**
     * @ORM\Column(name="creation_date", type="datetime")
     * @Groups({"api_cursus", "api_user_min"})
     * @SerializedName("creationDate")
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="edition_date", type="datetime", nullable=true)
     * @Groups({"api_cursus", "api_user_min"})
     * @SerializedName("editionDate")
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

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getSessionEvent()
    {
        return $this->sessionEvent;
    }

    public function setSessionEvent(SessionEvent $sessionEvent)
    {
        $this->sessionEvent = $sessionEvent;
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

<?php

namespace Claroline\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\UserMessageRepository")
 * @ORM\Table(name="claro_user_message")
 */
class UserMessage
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User",
     *     inversedBy="userMessages"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Message",
     *     inversedBy="userMessages"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $message;

    /**
     * @ORM\Column(name="is_removed", type="boolean")
     */
    protected $isRemoved;

    /**
     * @ORM\Column(name="is_read", type="boolean")
     */
    protected $isRead;

    /**
     * @ORM\Column(name="is_sent", type="boolean")
     */
    protected $isSent;

    public function __construct()
    {
        $this->isRead = false;
        $this->isRemoved = false;
        $this->isSent = false;
    }

    public function getId()
    {
        return $this->id;
    }

    public function markAsRemoved()
    {
        $this->isRemoved = true;
    }

    public function markAsUnremoved()
    {
        $this->isRemoved = false;
    }

    public function markAsRead()
    {
        $this->isRead = true;
    }

    public function isRemoved()
    {
        return $this->isRemoved;
    }

    public function isRead()
    {
        return $this->isRead;
    }

    public function isSent()
    {
        return $this->isSent;
    }

    public function setIsSent($isSent)
    {
        $this->isSent = $isSent;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUserMessages()
    {
        return $this->userMessages;
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="Claroline\MessageBundle\Repository\UserMessageRepository")
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
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\MessageBundle\Entity\Message",
     *     inversedBy="userMessages"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @JMS\Groups({"api_message"})
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

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_open_date", type="datetime", nullable=true)
     */
    protected $lastOpenDate;

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
        $now = new \DateTime();
        $this->setLastOpenDate($now);
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

    public function setLastOpenDate(\DateTime $date)
    {
        $this->lastOpenDate = $date;
    }

    public function getLastOpenDate()
    {
        return $this->lastOpenDate;
    }
}

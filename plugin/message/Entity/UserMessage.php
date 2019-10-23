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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="claro_user_message")
 */
class UserMessage
{
    use Id;
    use Uuid;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var User
     */
    private $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\MessageBundle\Entity\Message",
     *     inversedBy="userMessages"
     * )
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @var Message
     */
    private $message;

    /**
     * @ORM\Column(name="is_removed", type="boolean")
     *
     * @var bool
     */
    protected $isRemoved = false;

    /**
     * @ORM\Column(name="is_read", type="boolean")
     *
     * @var bool
     */
    protected $isRead = false;

    /**
     * @ORM\Column(name="is_sent", type="boolean")
     *
     * @var bool
     */
    protected $isSent = false;

    /**
     * @ORM\Column(name="last_open_date", type="datetime", nullable=true)
     *
     * @var \DateTime
     */
    protected $lastOpenDate;

    /**
     * UserMessage constructor.
     */
    public function __construct()
    {
        $this->refreshUuid();
    }

    public function setRemoved($removed)
    {
        $this->isRemoved = $removed;
    }

    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        if ($isRead) {
            $now = new \DateTime();
            $this->setLastOpenDate($now);
        }
    }

    //alias
    public function setRead($isRead)
    {
        $this->setIsRead($isRead);
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

    /**
     * @return User
     */
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

    public function setLastOpenDate(\DateTime $date)
    {
        $this->lastOpenDate = $date;
    }

    public function getLastOpenDate()
    {
        return $this->lastOpenDate;
    }
}

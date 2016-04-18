<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\ChatBundle\Repository\ChatRoomMessageRepository")
 * @ORM\Table(name="claro_chatbundle_room_message")
 */
class ChatRoomMessage
{
    const MESSAGE = 0;
    const PRESENCE = 1;
    const STATUS = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ChatBundle\Entity\ChatRoom",
     *     inversedBy="messages"
     * )
     * @ORM\JoinColumn(name="chat_room_id", onDelete="CASCADE", nullable=false)
     */
    protected $chatRoom;

    /**
     * @ORM\Column(name="username", nullable=false)
     */
    protected $username;

    /**
     * @ORM\Column(name="user_full_name", nullable=false)
     */
    protected $userFullName;

    /**
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    protected $content;

    /**
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    protected $creationDate;

    /**
     * @ORM\Column(name="message_type", type="integer", nullable=false)
     */
    protected $type = self::MESSAGE;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getChatRoom()
    {
        return $this->chatRoom;
    }

    public function setChatRoom(ChatRoom $chatRoom)
    {
        $this->chatRoom = $chatRoom;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getUserFullName()
    {
        return $this->userFullName;
    }

    public function setUserFullName($userFullName)
    {
        $this->userFullName = $userFullName;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getTypeText()
    {
        $typeText = '';

        switch ($this->type) {
            case self::MESSAGE :
                $typeText = 'message';
                break;
            case self::PRESENCE :
                $typeText = 'presence';
                break;
        }

        return $typeText;
    }
}

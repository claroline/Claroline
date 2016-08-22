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

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_chatbundle_room_resource")
 */
class ChatRoom extends AbstractResource
{
    const UNINITIALIZED = 0;
    const OPEN = 1;
    const CLOSED = 2;

    const TEXT = 0;
    const AUDIO = 1;
    const VIDEO = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_chat"})
     */
    protected $id;

    /**
     * @ORM\Column(name="room_name", nullable=true)
     * @Groups({"api_chat"})
     */
    protected $roomName;

    /**
     * @ORM\Column(type="integer", name="room_status")
     * @Groups({"api_chat"})
     */
    protected $roomStatus = self::UNINITIALIZED;

    /**
     * @ORM\Column(type="integer", name="room_type")
     * @Groups({"api_chat"})
     */
    protected $roomType = self::TEXT;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ChatBundle\Entity\ChatRoomMessage",
     *     mappedBy="chatRoom"
     * )
     */
    protected $messages;

    /**
     * @Accessor(getter="getRoomStatusText")
     * @Groups({"api_chat"})
     */
    protected $roomStatusText;

    /**
     * @Accessor(getter="getRoomTypeText")
     * @Groups({"api_chat"})
     */
    protected $roomTypeText;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRoomName()
    {
        return $this->roomName;
    }

    public function setRoomName($roomName)
    {
        $this->roomName = $roomName;
    }

    public function getRoomStatus()
    {
        return $this->roomStatus;
    }

    public function getRoomStatusText()
    {
        $status = '';

        switch ($this->roomStatus) {
            case self::UNINITIALIZED :
                $status = 'uninitialized';
                break;
            case self::OPEN :
                $status = 'open';
                break;
            case self::CLOSED :
                $status = 'closed';
                break;
            default:
                $status = 'unknown';
        }

        return $status;
    }

    public function setRoomStatus($roomStatus)
    {
        $this->roomStatus = $roomStatus;
    }

    public function getRoomType()
    {
        return $this->roomType;
    }

    public function setRoomType($roomType)
    {
        $this->roomType = $roomType;
    }

    public function getRoomTypeText()
    {
        $type = '';

        switch ($this->roomType) {
            case self::TEXT :
                $type = 'text';
                break;
            case self::AUDIO :
                $type = 'audio';
                break;
            case self::VIDEO :
                $type = 'video';
                break;
            default:
                $type = 'unknown';
        }

        return $type;
    }

    public function getMessages()
    {
        return $this->messages->toArray();
    }
}

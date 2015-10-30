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

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_chatbundle_room_resource")
 */
class ChatRoom extends AbstractResource
{
    const UNINITIALIZED = 0;
    const OPEN = 1;
    const CLOSED = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="room_name", nullable=true)
     */
    protected $roomName;

    /**
     * @ORM\Column(type="integer", name="room_status")
     */
    protected $roomStatus = self::UNINITIALIZED;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Claroline\ChatBundle\Entity\ChatRoomMessage",
     *     mappedBy="chatRoom"
     * )
     */
    protected $messages;

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

    public function setRoomStatus($roomStatus)
    {
        $this->roomStatus = $roomStatus;
    }

    public function getMessages()
    {
        return $this->messages->toArray();
    }
}

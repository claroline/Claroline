<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event;

use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class SendMessageEvent extends Event
{
    private $content;
    private $object;
    private $receiver;
    private $sender;
    private $users;
    private $withMail;

    public function __construct(
        User $sender,
        $content,
        $object,
        AbstractRoleSubject $receiver = null,
        array $users = array(),
        $withMail = true
    ) {
        $this->sender = $sender;
        $this->content = $content;
        $this->object = $object;
        $this->receiver = $receiver;
        $this->users = $users;
        $this->withMail = $withMail;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getObject()
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getReceiver()
    {
        return $this->receiver;
    }

    public function setReceiver(AbstractRoleSubject $receiver)
    {
        $this->receiver = $receiver;
    }

    public function getSender()
    {
        return $this->sender;
    }

    public function setSender(User $sender)
    {
        $this->sender = $sender;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers(array $users)
    {
        $this->users = $users;
    }

    public function getWithMail()
    {
        return $this->withMail;
    }

    public function setWithMail($withMail)
    {
        $this->withMail = $withMail;
    }
}

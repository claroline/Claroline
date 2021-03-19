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
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class SendMessageEvent extends Event
{
    private $content;
    private $object;
    private $receivers;
    private $sender;
    private $withMail;

    /**
     * @param AbstractRoleSubject[] $receivers
     */
    public function __construct(
        $content,
        $object,
        array $receivers,
        ?User $sender = null,
        $withMail = true
    ) {
        $this->content = $content;
        $this->object = $object;
        $this->receivers = $receivers;
        $this->sender = $sender;
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

    public function getReceivers()
    {
        return $this->receivers;
    }

    /**
     * @param AbstractRoleSubject[] $receiver
     */
    public function setReceivers(array $receivers)
    {
        $this->receivers = $receivers;
    }

    public function getSender()
    {
        return $this->sender;
    }

    public function setSender(User $sender)
    {
        $this->sender = $sender;
    }

    public function getWithMail()
    {
        return $this->withMail;
    }

    public function setWithMail($withMail)
    {
        $this->withMail = $withMail;
    }

    public function getMessage(TranslatorInterface $translator, $sender, User $receveir)
    {
        return $translator->trans(
            'sendMessage',
            [
                'sender' => $sender,
                'receveir' => $receveir,
            ],
            'platform'
        );
    }
}

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
    /** @var string */
    private $content;
    /** @var string */
    private $object;
    /** @var AbstractRoleSubject[] */
    private $receivers;
    /** @var User|null */
    private $sender;
    /** @var array */
    private $attachments;

    /**
     * @param AbstractRoleSubject[] $receivers
     */
    public function __construct(
        $content,
        $object,
        array $receivers,
        ?User $sender = null,
        array $attachments = []
    ) {
        $this->content = $content;
        $this->object = $object;
        $this->receivers = $receivers;
        $this->sender = $sender;
        $this->attachments = $attachments;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject($object)
    {
        $this->object = $object;
    }

    public function getReceivers(): array
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

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(User $sender)
    {
        $this->sender = $sender;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function addAttachment($attachment)
    {
        $this->attachments[] = $attachment;
    }

    public function getMessage(TranslatorInterface $translator, $sender, User $receiver): string
    {
        return $translator->trans(
            'sendMessage',
            [
                'sender' => $sender,
                'receiver' => $receiver,
            ],
            'platform'
        );
    }
}

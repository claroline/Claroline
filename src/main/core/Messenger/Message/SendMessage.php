<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Messenger\Message;

use Claroline\CoreBundle\Entity\User;

class SendMessage
{
    /** @var string */
    private $content;
    /** @var string */
    private $object;
    /** @var array */
    private $receivers;
    /** @var User|null */
    private $sender;

    public function __construct(string $content, string $object, array $receivers, ?User $sender = null)
    {
        $this->content = $content;
        $this->object = $object;
        $this->receivers = $receivers;
        $this->sender = $sender;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getObject(): string
    {
        return $this->object;
    }

    public function getReceivers(): array
    {
        return $this->receivers;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }
}

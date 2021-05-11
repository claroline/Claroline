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
use Claroline\MessageBundle\Entity\Message as MessageData;

class SendMessage
{
    private $content;
    private $object;
    private $users;

    public function __construct(string $content, string $object, array $users)
    {
        $this->content = $content;
        $this->object = $object;
        $this->users = $users;
    }

    public function createMessage()
    {
        $message = new MessageData();

        $message->setContent($this->content);
        $message->setParent(null);
        $message->setObject($this->object);
        $message->setSender(null);

        $message->setReceivers(array_map(function (User $user) {
            return $user->getUsername();
        }, $this->users));

        return $message;
    }
}

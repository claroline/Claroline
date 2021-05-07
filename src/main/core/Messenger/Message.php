<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Messenger;

use Claroline\MessageBundle\Entity\Message as MessageData;

class Message
{
    private $message;

    public function __construct(MessageData $message)
    {
        $this->message = $message;
    }

    public function getMessageData(): MessageData
    {
        return $this->message;
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Messenger;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class ForumNotification implements AsyncMessageInterface
{
    private $messageUuid;

    public function __construct(string $messageUuid)
    {
        $this->messageUuid = $messageUuid;
    }

    public function getMessageUuid(): string
    {
        return $this->messageUuid;
    }
}

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

use Claroline\MessageBundle\Entity\Message;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class NotificationHandler implements MessageHandlerInterface
{
    private $messageManager;

    public function __construct(MessageManager $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    public function __invoke(Message $message)
    {
        $this->messageManager->send($message);
    }
}

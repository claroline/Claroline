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

use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Claroline\MessageBundle\Manager\MessageManager;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ScheduledTaskHandler implements MessageHandlerInterface
{
    private $messageManager;
    private $taskManager;
    private $mailManager;

    public function __construct(
        MessageManager $messageManager,
        ScheduledTaskManager $taskManager,
        MailManager $mailManager
    ) {
        $this->messageManager = $messageManager;
        $this->taskManager = $taskManager;
        $this->mailManager = $mailManager;
    }

    public function __invoke(ScheduledTask $task)
    {
        $data = $task->getData();
        $users = $task->getUsers();
        $object = isset($data['object']) ? $data['object'] : null;
        $content = isset($data['content']) ? $data['content'] : null;

        if (!empty($users) && !empty($object) && !empty($content)) {

            if ($task->getType() === 'message') {
                $message = $this->messageManager->create($content, $object, $users);
                $this->messageManager->send($message);
            } else {
                $this->mailManager->send($object, $content, $users);
            }

            $this->taskManager->markAsExecuted($task);
        }
    }
}

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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Event\CatalogEvents\MessageEvents;
use Claroline\CoreBundle\Event\SendMessageEvent;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\Task\ScheduledTaskManager;
use Claroline\CoreBundle\Messenger\Message\ExecuteScheduledTask;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExecuteScheduledTaskHandler implements MessageHandlerInterface
{
    private $dispatcher;
    private $taskManager;
    private $mailManager;
    private $objectManager;

    public function __construct(
        StrictDispatcher $dispatcher,
        ScheduledTaskManager $taskManager,
        MailManager $mailManager,
        ObjectManager $objectManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->taskManager = $taskManager;
        $this->mailManager = $mailManager;
        $this->objectManager = $objectManager;
    }

    public function __invoke(ExecuteScheduledTask $scheduledTaskMessage)
    {
        $task = $this->objectManager->getRepository(ScheduledTask::class)->find($scheduledTaskMessage->getTaskId());

        $data = $task->getData();
        $users = $task->getUsers();
        $object = isset($data['object']) ? $data['object'] : null;
        $content = isset($data['content']) ? $data['content'] : null;

        if (!empty($users) && !empty($object) && !empty($content)) {
            if ('message' === $task->getType()) {
                $this->dispatcher->dispatch(MessageEvents::MESSAGE_SENDING, SendMessageEvent::class, [
                    $content,
                    $object,
                    $users,
                ]);
            } else {
                $this->mailManager->send($object, $content, $users);
            }

            $this->taskManager->markAsExecuted($task);
        }
    }
}

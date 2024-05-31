<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SchedulerBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Claroline\SchedulerBundle\Event\ExecuteScheduledTaskEvent;
use Claroline\SchedulerBundle\Manager\ScheduledTaskManager;
use Claroline\SchedulerBundle\Messenger\Message\ExecuteScheduledTask;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExecuteScheduledTaskHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $objectManager,
        private readonly ScheduledTaskManager $taskManager
    ) {
    }

    public function __invoke(ExecuteScheduledTask $scheduledTaskMessage): void
    {
        $task = $this->objectManager->getRepository(ScheduledTask::class)->find($scheduledTaskMessage->getTaskId());
        if (empty($task)) {
            return;
        }

        $event = new ExecuteScheduledTaskEvent($task);
        $event = $this->eventDispatcher->dispatch($event, 'scheduler.execute.'.$task->getAction());

        $this->taskManager->markAsExecuted($task, $event->getStatus());
    }
}

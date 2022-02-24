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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Claroline\SchedulerBundle\Event\ExecuteScheduledTaskEvent;
use Claroline\SchedulerBundle\Manager\ScheduledTaskManager;
use Claroline\SchedulerBundle\Messenger\Message\ExecuteScheduledTask;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class ExecuteScheduledTaskHandler implements MessageHandlerInterface
{
    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var ObjectManager */
    private $objectManager;
    /** @var ScheduledTaskManager */
    private $taskManager;

    public function __construct(
        StrictDispatcher $dispatcher,
        ObjectManager $objectManager,
        ScheduledTaskManager $taskManager
    ) {
        $this->dispatcher = $dispatcher;
        $this->objectManager = $objectManager;
        $this->taskManager = $taskManager;
    }

    public function __invoke(ExecuteScheduledTask $scheduledTaskMessage)
    {
        $task = $this->objectManager->getRepository(ScheduledTask::class)->find($scheduledTaskMessage->getTaskId());
        if (empty($task)) {
            return;
        }

        /** @var ExecuteScheduledTaskEvent $event */
        $event = $this->dispatcher->dispatch('scheduler.execute.'.$task->getAction(), ExecuteScheduledTaskEvent::class, [$task]);

        $this->taskManager->markAsExecuted($task, $event->getStatus());
    }
}

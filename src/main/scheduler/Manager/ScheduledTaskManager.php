<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SchedulerBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Claroline\SchedulerBundle\Messenger\Message\ExecuteScheduledTask;
use Claroline\SchedulerBundle\Repository\ScheduledTaskRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class ScheduledTaskManager
{
    /** @var ObjectManager */
    private $om;
    /** @var MessageBusInterface */
    private $messageBus;

    /** @var ScheduledTaskRepository */
    private $repository;

    public function __construct(
        ObjectManager $om,
        MessageBusInterface $messageBus
    ) {
        $this->om = $om;
        $this->messageBus = $messageBus;

        $this->repository = $om->getRepository(ScheduledTask::class);
    }

    public function execute(ScheduledTask $scheduledTask)
    {
        $scheduledTask->setStatus(ScheduledTask::IN_PROGRESS);
        $this->om->persist($scheduledTask);
        $this->om->flush();

        // request task execution
        $this->messageBus->dispatch(new ExecuteScheduledTask($scheduledTask->getId()));
    }

    /**
     * Flags a ScheduledTask as executed.
     */
    public function markAsExecuted(ScheduledTask $task, string $status)
    {
        $task->setExecutionDate(new \DateTime());
        $task->setStatus($status);

        if (ScheduledTask::RECURRING === $task->getExecutionType()) {
            // plan next execution of task to simplify the search of tasks to execute
            $task->setScheduledDate(
                $task->getScheduledDate()->add(new \DateInterval('P1D'))
            );
        }

        $this->om->persist($task);
        $this->om->flush();
    }

    /**
     * Retrieves the list of ScheduledTasks to execute.
     *
     * @return array
     */
    public function getTasksToExecute()
    {
        return $this->repository->findTasksToExecute();
    }
}

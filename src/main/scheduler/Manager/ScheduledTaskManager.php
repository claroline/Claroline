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
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Claroline\SchedulerBundle\Messenger\Message\ExecuteScheduledTask;
use Claroline\SchedulerBundle\Serializer\ScheduledTaskSerializer;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Claroline\SchedulerBundle\Repository\ScheduledTaskRepository;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @todo use CRUD instead.
 */
class ScheduledTaskManager
{
    /** @var ObjectManager */
    private $om;
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var PlatformConfigurationHandler */
    private $configHandler;
    /** @var ScheduledTaskRepository */
    private $repository;
    /** @var ScheduledTaskSerializer */
    private $serializer;

    public function __construct(
        ObjectManager $om,
        MessageBusInterface $messageBus,
        PlatformConfigurationHandler $configHandler,
        ScheduledTaskSerializer $serializer
    ) {
        $this->om = $om;
        $this->messageBus = $messageBus;
        $this->configHandler = $configHandler;
        $this->repository = $om->getRepository(ScheduledTask::class);
        $this->serializer = $serializer;
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
     * Creates a new ScheduledTask.
     *
     * @deprecated
     */
    public function create(array $data): ?ScheduledTask
    {
        return $this->update($data, new ScheduledTask());
    }

    /**
     * Updates a ScheduledTask.
     *
     * @deprecated
     */
    public function update(array $data, ScheduledTask $scheduledTask = null): ScheduledTask
    {
        $errors = $this->validate($data);
        if (count($errors) > 0) {
            throw new InvalidDataException('Scheduled task is not valid', $errors);
        }

        if (empty($scheduledTask)) {
            $scheduledTask = $this->om->getObject($data, ScheduledTask::class);
        }
        $scheduledTask = $this->serializer->deserialize($data, $scheduledTask);

        $this->om->persist($scheduledTask);
        $this->om->flush();

        return $scheduledTask;
    }

    /**
     * @deprecated
     */
    private function validate(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = ['path' => '/name', 'message' => 'name can not be empty.'];
        }

        if (empty($data['type'])) {
            $errors[] = ['path' => '/type', 'message' => 'type can not be empty.'];
        }

        if (empty($data['scheduledDate'])) {
            $errors[] = ['path' => '/scheduledDate', 'message' => 'scheduledDate can not be empty.'];
        }

        return $errors;
    }

    /**
     * Deletes a ScheduledTask.
     *
     * @deprecated
     */
    public function delete(ScheduledTask $scheduledTask)
    {
        $this->om->remove($scheduledTask);
        $this->om->flush();
    }

    /**
     * Flags a ScheduledTask as executed.
     */
    public function markAsExecuted(ScheduledTask $task, ?string $status = ScheduledTask::SUCCESS, ?\DateTime $executionDate = null)
    {
        if (empty($executionDate)) {
            $executionDate = new \DateTime();
        }

        $task->setExecutionDate($executionDate);
        $task->setStatus($status);

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

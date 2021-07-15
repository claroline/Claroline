<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Task;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Task\ScheduledTaskSerializer;
use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Repository\Task\ScheduledTaskRepository;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;

/**
 * @todo use CRUD instead.
 */
class ScheduledTaskManager
{
    /** @var ObjectManager */
    private $om;

    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /** @var ScheduledTaskRepository */
    private $repository;

    /** @var ScheduledTaskSerializer */
    private $serializer;

    public function __construct(
        ObjectManager $om,
        PlatformConfigurationHandler $configHandler,
        ScheduledTaskSerializer $serializer
    ) {
        $this->om = $om;
        $this->configHandler = $configHandler;
        $this->repository = $om->getRepository('ClarolineCoreBundle:Task\ScheduledTask');
        $this->serializer = $serializer;
    }

    /**
     * Serializes a ScheduledTask entity for the JSON api.
     */
    public function serialize(ScheduledTask $scheduledTask): array
    {
        return $this->serializer->serialize($scheduledTask);
    }

    /**
     * Creates a new ScheduledTask.
     */
    public function create(array $data): ?ScheduledTask
    {
        return $this->update($data, new ScheduledTask());
    }

    /**
     * Updates a ScheduledTask.
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

    public function validate(array $data): array
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
     */
    public function delete(ScheduledTask $scheduledTask)
    {
        $this->om->remove($scheduledTask);
        $this->om->flush();
    }

    /**
     * Deletes a list of ScheduledTasks.
     *
     * @param ScheduledTask[] $scheduledTasks
     */
    public function deleteBulk(array $scheduledTasks)
    {
        $this->om->startFlushSuite();
        foreach ($scheduledTasks as $scheduledTask) {
            $this->delete($scheduledTask);
        }
        $this->om->endFlushSuite();
    }

    /**
     * Flags a ScheduledTask as executed.
     */
    public function markAsExecuted(ScheduledTask $task, \DateTime $executionDate = null)
    {
        if (empty($executionDate)) {
            $executionDate = new \DateTime();
        }

        $task->setExecutionDate($executionDate);

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

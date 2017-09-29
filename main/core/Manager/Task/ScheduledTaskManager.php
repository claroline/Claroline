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

use Claroline\CoreBundle\API\Serializer\Task\ScheduledTaskSerializer;
use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Library\Validation\Exception\InvalidDataException;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Repository\Task\ScheduledTaskRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.scheduled_task_manager")
 */
class ScheduledTaskManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ScheduledTaskRepository */
    private $repository;

    /** @var ScheduledTaskSerializer */
    private $serializer;

    /**
     * ScheduledTaskManager constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.serializer.scheduled_task")
     * })
     *
     * @param ObjectManager           $om
     * @param ScheduledTaskSerializer $serializer
     */
    public function __construct(
        ObjectManager $om,
        ScheduledTaskSerializer $serializer)
    {
        $this->om = $om;
        $this->repository = $om->getRepository('ClarolineCoreBundle:Task\ScheduledTask');
        $this->serializer = $serializer;
    }

    /**
     * Serializes a ScheduledTask entity for the JSON api.
     *
     * @param ScheduledTask $scheduledTask - the task to serialize
     *
     * @return array - the serialized representation of the task
     */
    public function serialize(ScheduledTask $scheduledTask)
    {
        return $this->serializer->serialize($scheduledTask);
    }

    /**
     * Creates a new ScheduledTask.
     *
     * @param array $data
     *
     * @return ScheduledTask
     */
    public function create(array $data)
    {
        return $this->update($data, new ScheduledTask());
    }

    /**
     * Updates a ScheduledTask.
     *
     * @param array         $data
     * @param ScheduledTask $scheduledTask
     *
     * @return ScheduledTask
     *
     * @throws InvalidDataException
     */
    public function update(array $data, ScheduledTask $scheduledTask)
    {
        $errors = $this->validate($data);
        if (count($errors) > 0) {
            throw new InvalidDataException('Scheduled task is not valid', $errors);
        }

        $this->serializer->deserialize($data, $scheduledTask);

        $this->om->persist($scheduledTask);
        $this->om->flush();

        return $scheduledTask;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function validate(array $data)
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
     * @param ScheduledTask $scheduledTask
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
     *
     * @param ScheduledTask $task
     * @param \DateTime     $executionDate
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

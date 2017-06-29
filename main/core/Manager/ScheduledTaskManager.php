<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.scheduled_task_manager")
 */
class ScheduledTaskManager
{
    private $om;
    private $scheduledTaskRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->scheduledTaskRepo = $om->getRepository('ClarolineCoreBundle:Task\ScheduledTask');
    }

    public function searchTasksPartialList(array $searches, $page, $limit)
    {
        $data = [];
        // retrieves searchable text fields
        $baseFieldsName = ScheduledTask::getSearchableFields();

        /** @var QueryBuilder $qb */
        $qb = $this->om->createQueryBuilder();
        $qb->select('st');
        $qb->from('Claroline\CoreBundle\Entity\Task\ScheduledTask', 'st');

        if (!empty($searches['filters']) && is_array($searches['filters'])) {
            foreach ($searches['filters'] as $filterName => $filterValue) {
                if (in_array($filterName, $baseFieldsName)) {
                    $qb->andWhere("UPPER(st.{$filterName}) LIKE :{$filterName}");
                    $qb->setParameter($filterName, '%'.strtoupper($filterValue).'%');
                } else {
                    // catch boolean
                    if ('true' === $filterValue || 'false' === $filterValue) {
                        $filterValue = 'true' === $filterValue;
                    }

                    $qb->andWhere("st.{$filterName} = :{$filterName}");
                    $qb->setParameter($filterName, $filterValue);
                }
            }
        }

        if (!empty($searches['sortBy'])) {
            // reverse order starts by a -
            if ('-' === substr($searches['sortBy'], 0, 1)) {
                $qb->orderBy('st.'.substr($searches['sortBy'], 1), 'ASC');
            } else {
                $qb->orderBy('st.'.$searches['sortBy'], 'DESC');
            }
        }

        $query = $qb->getQuery();
        $data['count'] = count($query->getResult());

        if (!is_null($page) && !is_null($limit)) {
            //react table all is -1
            if ($limit > -1) {
                $query->setMaxResults($limit);
            }
            $query->setFirstResult($page * $limit);
        }
        $data['tasks'] = $query->getResult();

        return $data;
    }

    public function createScheduledTask(
        $type,
        \DateTime $scheduledDate,
        $data = null,
        $name = null,
        array $users = [],
        Group $group = null,
        Workspace $workspace = null
    ) {
        $task = new ScheduledTask();
        $task->setType($type);
        $task->setScheduledDate($scheduledDate);
        $task->setData($data);
        $task->setName($name);

        foreach ($users as $user) {
            $task->addUser($user);
        }
        $task->setGroup($group);
        $task->setWorkspace($workspace);
        $this->persistScheduledTask($task);

        return $task;
    }

    public function editScheduledTask(
        ScheduledTask $task,
        \DateTime $scheduledDate,
        $data = null,
        $name = null,
        array $users = [],
        Group $group = null,
        Workspace $workspace = null
    ) {
        $task->setScheduledDate($scheduledDate);
        $task->setData($data);
        $task->setName($name);

        foreach ($users as $user) {
            $task->addUser($user);
        }
        $task->setGroup($group);
        $task->setWorkspace($workspace);
        $this->persistScheduledTask($task);

        return $task;
    }

    public function persistScheduledTask(ScheduledTask $task)
    {
        $this->om->persist($task);
        $this->om->flush();
    }

    public function deleteScheduledTasks(array $tasks)
    {
        $this->om->startFlushSuite();

        foreach ($tasks as $task) {
            $this->om->remove($task);
        }
        $this->om->endFlushSuite();
    }

    public function markTaskAsExecuted(ScheduledTask $task, \DateTime $executionDate = null)
    {
        if (empty($executionDate)) {
            $executionDate = new \DateTime();
        }
        $task->setExecuted(true);
        $task->setExecutionDate($executionDate);
        $this->persistScheduledTask($task);
    }

    /*********************************************
     * Access to ScheduledTaskRepository methods *
     *********************************************/

    public function getTasksToExecute()
    {
        return $this->scheduledTaskRepo->findTasksToExecute();
    }
}

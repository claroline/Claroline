<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SchedulerBundle\Repository;

use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Doctrine\ORM\EntityRepository;

class ScheduledTaskRepository extends EntityRepository
{
    public function findTasksToExecute()
    {
        return $this->_em
            ->createQuery('
                SELECT t
                FROM Claroline\SchedulerBundle\Entity\ScheduledTask t
                WHERE (
                    (t.executionType = ":once" AND t.executionDate IS NULL)) 
                    OR 
                    ((t.executionType = ":recurring" AND (t.endDate IS NULL OR t.endDate < :now))
                )  
                AND t.scheduledDate < :now
            ')
            ->setParameter('now', new \DateTime())
            ->setParameter('once', ScheduledTask::ONCE)
            ->setParameter('recurring', ScheduledTask::RECURRING)
            ->getResult();
    }
}

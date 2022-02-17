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

use Doctrine\ORM\EntityRepository;

class ScheduledTaskRepository extends EntityRepository
{
    public function findTasksToExecute()
    {
        $dql = '
            SELECT t
            FROM Claroline\SchedulerBundle\Entity\ScheduledTask t
            WHERE t.executionDate IS NULL
            AND t.scheduledDate < :now
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('now', new \DateTime());

        return $query->getResult();
    }
}

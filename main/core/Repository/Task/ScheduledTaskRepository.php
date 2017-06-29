<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Task;

use Doctrine\ORM\EntityRepository;

class ScheduledTaskRepository extends EntityRepository
{
    public function findTasksToExecute()
    {
        $dql = '
            SELECT t
            FROM Claroline\CoreBundle\Entity\Task\ScheduledTask t
            WHERE t.executed = false
            AND t.scheduledDate < :now
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('now', new \DateTime());

        return $query->getResult();
    }
}

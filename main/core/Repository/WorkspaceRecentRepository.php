<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 9/8/17
 * Time: 3:43 PM.
 */

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

class WorkspaceRecentRepository extends EntityRepository
{
    public function removeAllEntriesBefore($date)
    {
        $qb = $this
            ->createQueryBuilder('rw')
            ->delete()
            ->andWhere('rw.entryDate <= :date')
            ->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }
}

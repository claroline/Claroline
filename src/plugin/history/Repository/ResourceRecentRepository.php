<?php

namespace Claroline\HistoryBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class ResourceRecentRepository extends EntityRepository
{
    public function findEntries(User $user, int $nbResults)
    {
        return $this->createQueryBuilder('r')
            ->where('r.user = :user')
            ->orderBy('r.createdAt', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults($nbResults)
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function removeAllEntriesBefore($date)
    {
        $qb = $this
            ->createQueryBuilder('rw')
            ->delete()
            ->andWhere('rw.createdAt <= :date')
            ->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }
}

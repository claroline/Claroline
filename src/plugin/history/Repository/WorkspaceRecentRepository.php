<?php

namespace Claroline\HistoryBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class WorkspaceRecentRepository extends EntityRepository
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
}

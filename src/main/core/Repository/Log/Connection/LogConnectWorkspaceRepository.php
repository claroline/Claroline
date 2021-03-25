<?php

namespace Claroline\CoreBundle\Repository\Log\Connection;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class LogConnectWorkspaceRepository extends EntityRepository
{
    public function countConnections(Workspace $workspace): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->join('c.user', 'user')
            ->where('user.isEnabled = true')
            ->andWhere('user.isRemoved = false')
            ->andWhere('c.workspace = :workspace')
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAvgTime(Workspace $workspace): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('AVG(c.duration)')
            ->join('c.user', 'user')
            ->where('user.isEnabled = true')
            ->andWhere('user.isRemoved = false')
            ->andWhere('c.workspace = :workspace')
            ->andWhere('c.duration IS NOT NULL')
            ->setParameter('workspace', $workspace)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

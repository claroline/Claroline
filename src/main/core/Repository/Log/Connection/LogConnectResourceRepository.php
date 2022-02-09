<?php

namespace Claroline\CoreBundle\Repository\Log\Connection;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\EntityRepository;

class LogConnectResourceRepository extends EntityRepository
{
    public function countConnections(ResourceNode $resourceNode): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->join('c.user', 'user')
            ->where('user.isEnabled = true')
            ->andWhere('user.isRemoved = false')
            ->andWhere('c.resource = :resourceNode')
            ->setParameter('resourceNode', $resourceNode)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAvgTime(ResourceNode $resourceNode): int
    {
        return (int) $this->createQueryBuilder('c')
            ->select('AVG(c.duration)')
            ->join('c.user', 'user')
            ->where('user.isEnabled = true')
            ->andWhere('user.isRemoved = false')
            ->andWhere('c.resource = :resourceNode')
            ->andWhere('c.duration IS NOT NULL')
            ->setParameter('resourceNode', $resourceNode)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

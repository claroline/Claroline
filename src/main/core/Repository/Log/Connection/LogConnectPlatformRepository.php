<?php

namespace Claroline\CoreBundle\Repository\Log\Connection;

use Claroline\CoreBundle\Entity\Organization\Organization;
use Doctrine\ORM\EntityRepository;

class LogConnectPlatformRepository extends EntityRepository
{
    public function countConnections(array $organizations = []): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('COUNT(c)')
            ->join('c.user', 'user')
            ->where('user.isEnabled = true')
            ->andWhere('user.isRemoved = false');

        if (!empty($organizations)) {
            $qb
                ->join('user.userOrganizationReferences', 'orgaRef')
                ->andWhere('orgaRef.organization IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param Organization[] $organizations
     */
    public function findAvgTime(array $organizations = []): int
    {
        $qb = $this->createQueryBuilder('c')
            ->select('AVG(c.duration)')
            ->join('c.user', 'user')
            ->where('user.isEnabled = true')
            ->andWhere('user.isRemoved = false')
            ->andWhere('c.duration IS NOT NULL');

        if (!empty($organizations)) {
            $qb
                ->join('user.userOrganizationReferences', 'orgaRef')
                ->andWhere('orgaRef.organization IN (:organizations)')
                ->setParameter('organizations', $organizations);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}

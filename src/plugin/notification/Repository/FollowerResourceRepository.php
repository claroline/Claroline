<?php

namespace Icap\NotificationBundle\Repository;

use Doctrine\ORM\EntityRepository;

class FollowerResourceRepository extends EntityRepository
{
    public function findFollowersByResourceIdAndClass($resourceId, $resourceClass)
    {
        $queryBuilder = $this->createQueryBuilder('followerResource');
        $queryBuilder
            ->select('followerResource.followerId AS id')
            ->andWhere('followerResource.resourceId = :resourceId')
            ->andWhere('followerResource.resourceClass = :resourceClass')
            ->setParameter('resourceId', $resourceId)
            ->setParameter('resourceClass', $resourceClass);

        return $queryBuilder->getQuery()->getArrayResult();
    }
}

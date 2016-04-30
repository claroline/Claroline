<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 4/8/15
 */

namespace Icap\NotificationBundle\Repository;

use Doctrine\ORM\EntityRepository;

class NotificationUserParametersRepository extends EntityRepository
{
    public function findParametersByUserId($userId)
    {
        $qb = $this->createQueryBuilder('parameters');
        $qb
            ->select('parameters')
            ->andWhere('parameters.userId = :userId')
            ->setParameter('userId', $userId);

        return $qb->getQuery()->getSingleResult();
    }
}

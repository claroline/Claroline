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

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class NotificationUserParametersRepository extends EntityRepository
{
    public function findParametersByUser(User $user)
    {
        $qb = $this->createQueryBuilder('parameters');
        $qb
            ->select('parameters')
            ->andWhere('parameters.user = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getSingleResult();
    }
}

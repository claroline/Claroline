<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/8/17
 */

namespace Claroline\CasBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CasUserRepository extends EntityRepository
{
    public function unlinkCasUser($userId)
    {
        $qb = $this->createQueryBuilder('cas');
        $qb
            ->delete()
            ->andWhere('cas.user = :user')
            ->setParameter('user', $userId);

        $qb->getQuery()->execute();
    }

    public function findCasUsersByCasIds($casIds)
    {
        $qb = $this
            ->createQueryBuilder('cas')
            ->where('cas.casId IN (:ids)')
            ->setParameter('ids', $casIds);

        return $qb->getQuery()->getResult();
    }

    public function findCasUsersByCasIdsOrUserIds($casIds, $userIds)
    {
        $qb = $this
            ->createQueryBuilder('cas')
            ->where('cas.casId IN (:ids)')
            ->orWhere('cas.user IN (:users)')
            ->setParameter('ids', $casIds)
            ->setParameter('users', $userIds);

        return $qb->getQuery()->getResult();
    }
}

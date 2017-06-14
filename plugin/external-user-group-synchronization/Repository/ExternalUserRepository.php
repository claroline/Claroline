<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 5/17/17
 */

namespace Claroline\ExternalSynchronizationBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ExternalUserRepository extends EntityRepository
{
    public function findByExternalIdsAndSourceSlug($externalIds, $sourceSlug)
    {
        if (empty($externalIds)) {
            return [];
        }

        $qb = $this
            ->createQueryBuilder('ext_user')
            ->where('ext_user.sourceSlug = :source')
            ->andWhere('ext_user.externalUserId IN (:ids)')
            ->setParameter('source', $sourceSlug)
            ->setParameter('ids', $externalIds);

        return $qb->getQuery()->getResult();
    }

    public function deleteBySourceSlug($source)
    {
        $qb = $this
            ->createQueryBuilder('ext_user')
            ->delete()
            ->where('ext_user.sourceSlug = :source')
            ->setParameter('source', $source);

        $qb->getQuery()->execute();
    }

    public function updateSourceSlug($oldSource, $newSource)
    {
        $qb = $this->createQueryBuilder('ext_user')
            ->update()
            ->set('ext_user.sourceSlug', ':newSource')
            ->where('ext_user.sourceSlug = :oldSource')
            ->setParameter('newSource', $newSource)
            ->setParameter('oldSource', $oldSource);

        $qb->getQuery()->execute();
    }

    public function countBySearchForSource($source, $search = '')
    {
        $qb = $this->createQueryBuilder('ext_user')
            ->select('COUNT(ext_user.id) AS cnt')
            ->where('ext_user.sourceSlug = :source')
            ->setParameter('source', $source);
        if (!empty($search)) {
            $qb
                ->innerJoin('ext_user.user', 'user')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like('UPPER(user.username)', ':search'),
                    $qb->expr()->like('UPPER(user.firstName)', ':search'),
                    $qb->expr()->like('UPPER(user.lastName)', ':search'),
                    $qb->expr()->like('UPPER(user.mail)', ':search')
                ))
                ->setParameter('search', '%'.strtoupper($search).'%');
        }

        return intval($qb->getQuery()->getSingleResult()['cnt']);
    }

    public function searchForSourcePaginated(
        $source,
        $page = 1,
        $max = 50,
        $orderBy = 'username',
        $direction = 'ASC',
        $search = ''
    ) {
        $page = max(0, $page - 1);

        $qb = $this->createQueryBuilder('ext_user')
            ->innerJoin('ext_user.user', 'user')
            ->select('user.username, user.firstName, user.lastName, user.mail, user.administrativeCode')
            ->where('ext_user.sourceSlug = :source')
            ->setMaxResults($max)
            ->setFirstResult($max * $page)
            ->orderBy('user.'.$orderBy, $direction)
            ->setParameter('source', $source);
        if (!empty($search)) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like('UPPER(user.username)', ':search'),
                    $qb->expr()->like('UPPER(user.firstName)', ':search'),
                    $qb->expr()->like('UPPER(user.lastName)', ':search'),
                    $qb->expr()->like('UPPER(user.mail)', ':search')
                ))
                ->setParameter('search', '%'.strtoupper($search).'%');
        }

        return $qb->getQuery()->getResult();
    }

    public function deleteExternalUserByUserId($userId)
    {
        $qb = $this->createQueryBuilder('ext_user')
            ->delete()
            ->where('ext_user.user = :user')
            ->setParameter('user', $userId);

        $qb->getQuery()->execute();
    }
}

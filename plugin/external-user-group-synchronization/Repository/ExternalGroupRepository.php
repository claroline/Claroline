<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 4/12/17
 */

namespace Claroline\ExternalSynchronizationBundle\Repository;

use Claroline\ExternalSynchronizationBundle\Entity\ExternalGroup;
use Doctrine\ORM\EntityRepository;

class ExternalGroupRepository extends EntityRepository
{
    public function findByRolesAndSearch(
        array $roles,
        $search = null,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $qb = $this
            ->createQueryBuilder('ext_group')
            ->innerJoin('ext_group.group', 'intGroup')
            ->innerJoin('intGroup.roles', 'role')
            ->where('role IN (:roles)')
            ->orderBy("intGroup.{$orderedBy}", $order)
            ->setParameter('roles', $roles);
        if (!empty($search)) {
            $search = strtoupper($search);
            $qb
                ->andWhere($qb->expr()->like('UPPER(intGroup.name)', ':search'))
                ->setParameter('search', "%{$search}%");
        }

        return $executeQuery ? $qb->getQuery()->getResult() : $qb->getQuery();
    }

    public function deactivateGroupsForSource($source)
    {
        $now = new \DateTime();
        $now->setTime(0, 0, 0);

        $qb = $this
            ->createQueryBuilder('ext_group')
            ->update()
            ->set('ext_group.active', ':inactive')
            ->where('ext_group.sourceSlug = :source')
            ->andWhere('ext_group.lastSynchronizationDate < :now')
            ->setParameter('inactive', false)
            ->setParameter('source', $source)
            ->setParameter('now', $now);

        $qb->getQuery()->execute();
    }

    public function deleteBySourceSlug($source)
    {
        $qb = $this
            ->createQueryBuilder('ext_group')
            ->delete()
            ->where('ext_group.sourceSlug = :source')
            ->setParameter('source', $source);

        $qb->getQuery()->execute();
    }

    public function updateSourceSlug($oldSource, $newSource)
    {
        $qb = $this->createQueryBuilder('ext_group')
            ->update()
            ->set('ext_group.sourceSlug', ':newSource')
            ->where('ext_group.sourceSlug = :oldSource')
            ->setParameter('newSource', $newSource)
            ->setParameter('oldSource', $oldSource);

        $qb->getQuery()->execute();
    }

    public function countBySearchForSource($source, $search = '')
    {
        $qb = $this->createQueryBuilder('ext_group')
            ->select('COUNT(ext_group.id) AS cnt')
            ->where('ext_group.sourceSlug = :source')
            ->setParameter('source', $source);
        if (!empty($search)) {
            $qb
                ->innerJoin('ext_group.group', 'grp')
                ->andWhere($qb->expr()->like('UPPER(grp.name)', ':search'))
                ->setParameter('search', '%'.strtoupper($search).'%');
        }

        return intval($qb->getQuery()->getSingleResult()['cnt']);
    }

    public function searchForSourcePaginated(
        $source,
        $page = 1,
        $max = 50,
        $orderBy = 'name',
        $direction = 'ASC',
        $search = ''
    ) {
        $page = max(0, $page - 1);

        $qb = $this->createQueryBuilder('ext_group')
            ->innerJoin('ext_group.group', 'grp')
            ->leftJoin('grp.users', 'usr', false)
            ->select('ext_group.id, grp.name', 'COUNT(usr) AS user_count')
            ->where('ext_group.sourceSlug = :source')
            ->setMaxResults($max)
            ->setFirstResult($max * $page)
            ->orderBy('grp.'.$orderBy, $direction)
            ->groupBy('grp')
            ->setParameter('source', $source);
        if (!empty($search)) {
            $qb
                ->andWhere($qb->expr()->like('UPPER(grp.name)', ':search'))
                ->setParameter('search', '%'.strtoupper($search).'%');
        }

        return $qb->getQuery()->getResult();
    }

    public function countUsersInGroup(ExternalGroup $externalGroup)
    {
        $qb = $this->createQueryBuilder('ext_group')
            ->innerJoin('ext_group.group', 'grp')
            ->leftJoin('grp.users', 'usr', false)
            ->select('COUNT(usr) AS cnt')
            ->where('ext_group = :group')
            ->setParameter('group', $externalGroup);

        return intval($qb->getQuery()->getSingleResult()['cnt']);
    }
}

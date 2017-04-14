<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Repository;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CursusBundle\Entity\Cursus;
use Doctrine\ORM\EntityRepository;

class CursusGroupRepository extends EntityRepository
{
    public function findCursusGroupsByCursus(
        Cursus $cursus,
        $executeQuery = true
    ) {
        $dql = '
            SELECT cg
            FROM Claroline\CursusBundle\Entity\CursusGroup cg
            WHERE cg.cursus = :cursus
            ORDER BY cg.registrationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findOneCursusGroupByCursusAndGroup(
        Cursus $cursus,
        Group $group,
        $executeQuery = true
    ) {
        $dql = '
            SELECT cg
            FROM Claroline\CursusBundle\Entity\CursusGroup cg
            WHERE cg.cursus = :cursus
            AND cg.group = :group
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);
        $query->setParameter('group', $group);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findCursusGroupsFromCursusAndGroups(
        array $cursus,
        array $groups
    ) {
        $dql = '
            SELECT cg
            FROM Claroline\CursusBundle\Entity\CursusGroup cg
            WHERE cg.cursus IN (:cursus)
            AND cg.group IN (:groups)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);
        $query->setParameter('groups', $groups);

        return $query->getResult();
    }

    public function findCursusGroupsOfCursusChildren(
        Cursus $cursus,
        Group $group,
        $executeQuery = true
    ) {
        $dql = '
            SELECT cg
            FROM Claroline\CursusBundle\Entity\CursusGroup cg
            JOIN cg.cursus c
            WHERE cg.group = :group
            AND c.parent = :cursus
            AND c.root = :root
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('group', $group);
        $query->setParameter('cursus', $cursus);
        $query->setParameter('root', $cursus->getRoot());

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUnregisteredGroupsByCursus(
        Cursus $cursus,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT g
            FROM Claroline\CoreBundle\Entity\Group g
            WHERE NOT EXISTS (
                SELECT cg
                FROM Claroline\CursusBundle\Entity\CursusGroup cg
                WHERE cg.cursus = :cursus
                AND cg.group = g
            )
            ORDER BY g.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedUnregisteredGroupsByCursus(
        Cursus $cursus,
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT g
            FROM Claroline\CoreBundle\Entity\Group g
            WHERE UPPER(g.name) LIKE :search
            AND NOT EXISTS (
                SELECT cg
                FROM Claroline\CursusBundle\Entity\CursusGroup cg
                WHERE cg.cursus = :cursus
                AND cg.group = g
            )
            ORDER BY g.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUnregisteredGroupsByCursusAndOrganizations(
        Cursus $cursus,
        array $organizations,
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT g
            FROM Claroline\CoreBundle\Entity\Group g
            JOIN g.organizations go
            WHERE go IN (:organizations)
            AND NOT EXISTS (
                SELECT cg
                FROM Claroline\CursusBundle\Entity\CursusGroup cg
                WHERE cg.cursus = :cursus
                AND cg.group = g
            )
            ORDER BY g.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);
        $query->setParameter('organizations', $organizations);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedUnregisteredGroupsByCursusAndOrganizations(
        Cursus $cursus,
        array $organizations,
        $search = '',
        $orderedBy = 'name',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT g
            FROM Claroline\CoreBundle\Entity\Group g
            JOIN g.organizations go
            WHERE go IN (:organizations)
            AND UPPER(g.name) LIKE :search
            AND NOT EXISTS (
                SELECT cg
                FROM Claroline\CursusBundle\Entity\CursusGroup cg
                WHERE cg.cursus = :cursus
                AND cg.group = g
            )
            ORDER BY g.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);
        $query->setParameter('organizations', $organizations);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findCursusGroupsByIds(array $ids, $executeQuery = true)
    {
        $dql = '
            SELECT DISTINCT cg
            FROM Claroline\CursusBundle\Entity\CursusGroup cg
            WHERE cg.id IN (:ids)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('ids', $ids);

        return $executeQuery ? $query->execute() : $query;
    }
}

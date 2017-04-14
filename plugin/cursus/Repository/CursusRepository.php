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
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CursusRepository extends NestedTreeRepository
{
    public function findAllCursus($orderedBy = 'cursusOrder', $order = 'ASC', $executeQuery = true)
    {
        $dql = "
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedCursus(
        $search = '',
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            LEFT JOIN c.course cc
            WHERE UPPER(c.title) LIKE :search
            OR UPPER(c.code) LIKE :search
            OR UPPER(cc.code) LIKE :search
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findAllRootCursus(
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.parent IS NULL
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findAllRootCursusByOrganizations(array $organizations, $orderedBy = 'id', $order = 'ASC')
    {
        $dql = "
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            JOIN c.organizations o
            WHERE c.parent IS NULL
            AND o IN (:organizations)
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('organizations', $organizations);

        return $query->getResult();
    }

    public function findLastRootCursusOrder($executeQuery = true)
    {
        $dql = '
            SELECT Max(c.cursusOrder)
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.parent IS NULL
        ';
        $query = $this->_em->createQuery($dql);

        return $executeQuery ?
            $query->getSingleScalarResult() :
            $query;
    }

    public function findLastCursusOrderByParent(Cursus $cursus, $executeQuery = true)
    {
        $dql = '
            SELECT Max(c.cursusOrder)
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.parent = :parent
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('parent', $cursus);

        return $executeQuery ?
            $query->getSingleScalarResult() :
            $query;
    }

    public function findRelatedHierarchyByCursus(
        Cursus $cursus,
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.root = :root
            AND (
                (c.lft <= :left AND c.rgt >= :right)
                OR (c.lft >= :left AND c.rgt <= :right)
            )
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('root', $cursus->getRoot());
        $query->setParameter('left', $cursus->getLft());
        $query->setParameter('right', $cursus->getRgt());

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findDescendantHierarchyByCursus(
        Cursus $cursus,
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.root = :root
            AND c.lft >= :left
            AND c.rgt <= :right
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('root', $cursus->getRoot());
        $query->setParameter('left', $cursus->getLft());
        $query->setParameter('right', $cursus->getRgt());

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findCursusByIds(array $ids, $executeQuery = true)
    {
        $dql = '
            SELECT DISTINCT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.id IN (:ids)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('ids', $ids);

        return $executeQuery ? $query->execute() : $query;
    }

    public function findCursusByGroup(Group $group, $executeQuery = true)
    {
        $dql = '
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE EXISTS (
                SELECT cg
                FROM Claroline\CursusBundle\Entity\CursusGroup cg
                JOIN cg.group g
                WHERE g.name = :group
                AND cg.cursus = c
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('group', $group->getName());

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findCursusByCodeWithoutId($code, $id, $executeQuery = true)
    {
        $dql = "
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE UPPER(c.code) = :code
            AND c.id != :id
        ";
        $query = $this->_em->createQuery($dql);
        $upperCode = strtoupper($code);
        $query->setParameter('code', $upperCode);
        $query->setParameter('id', $id);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}

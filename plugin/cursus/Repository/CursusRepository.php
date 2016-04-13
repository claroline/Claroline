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
    public function findAllCursus(
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $executeQuery = true
    ) {
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

    public function findSearchedRootCursus(
        $search = '',
        $orderedBy = 'id',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.parent IS NULL
            AND (
                UPPER(c.title) LIKE :search
                OR UPPER(c.code) LIKE :search
                OR UPPER(cc.code) LIKE :search
            )
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
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

    public function findHierarchyByCursus(
        Cursus $cursus,
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.root = :root
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('root', $cursus->getRoot());

        return $executeQuery ? $query->getResult() : $query;
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

    public function findCursusByParentAndCourses(
        Cursus $parent,
        array $courses,
        $executeQuery = true
    ) {
        $dql = '
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.parent = :parent
            AND c.course IN (:courses)
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('parent', $parent);
        $query->setParameter('courses', $courses);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function updateCursusOrderByParent(
        Cursus $parent,
        $cursusOrder,
        $executeQuery = true
    ) {
        $dql = '
            UPDATE Claroline\CursusBundle\Entity\Cursus c
            SET c.cursusOrder = c.cursusOrder + 1
            WHERE c.parent = :parent
            AND c.cursusOrder >= :cursusOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('parent', $parent);
        $query->setParameter('cursusOrder', $cursusOrder);

        return $executeQuery ? $query->execute() : $query;
    }

    public function updateCursusOrderWithoutParent(
        $cursusOrder,
        $executeQuery = true
    ) {
        $dql = '
            UPDATE Claroline\CursusBundle\Entity\Cursus c
            SET c.cursusOrder = c.cursusOrder + 1
            WHERE c.parent IS NULL
            AND c.cursusOrder >= :cursusOrder
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursusOrder', $cursusOrder);

        return $executeQuery ? $query->execute() : $query;
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

    public function findOneCursusById($cursusId, $executeQuery = true)
    {
        $dql = '
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.id = :cursusId
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursusId', $cursusId);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findCursusByGroup(Group $group, $executeQuery = true)
    {
        $dql = '
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE EXISTS (
                SELECT cg
                FROM Claroline\CursusBundle\Entity\CursusGroup cg
                WHERE cg.group = :group
                AND cg.cursus = c
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('group', $group);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findCursusByParent(
        Cursus $parent,
        $orderedBy = 'cursusOrder',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.parent = :parent
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('parent', $parent);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSearchedCursusByParent(
        Cursus $parent,
        $search = '',
        $orderedBy = 'title',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            LEFT JOIN c.course cc
            WHERE c.parent = :parent
            AND (
                UPPER(c.title) LIKE :search
                OR UPPER(c.code) LIKE :search
                OR UPPER(cc.code) LIKE :search
            )
            ORDER BY c.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('parent', $parent);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }
}

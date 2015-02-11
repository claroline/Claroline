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

use Claroline\CursusBundle\Entity\Cursus;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CursusRepository extends NestedTreeRepository
{
    public function findAllRootCursus($executeQuery = true)
    {
    	$dql = '
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.parent IS NULL
        ';
    	$query = $this->_em->createQuery($dql);

    	return $executeQuery ?
            $query->getResult() :
            $query;
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
    )
    {
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

    public function findCursusByParentAndCourses(
        Cursus $parent,
        array $courses,
        $executeQuery = true
    )
    {
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
    )
    {
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
    )
    {
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
}

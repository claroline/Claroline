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

    public function findHierarchyByCursus(Cursus $cursus, $executeQuery = true)
    {
        $dql = '
            SELECT c
            FROM Claroline\CursusBundle\Entity\Cursus c
            WHERE c.root = :root
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('root', $cursus->getRoot());

        return $executeQuery ? $query->getResult() : $query;
    }
}

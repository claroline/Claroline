<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\Competence\Competence;
use Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CompetenceRepository extends NestedTreeRepository {

    public function excludeHierarchyNode(CompetenceHierarchy $cpt)
    {
        $dql = "
        SELECT c.name as name , cp.id as id 
        FROM Claroline\CoreBundle\Entity\Competence\Competence c,
        	 Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy cp
        WHERE 
        	c.id = cp.competence
        	AND cp.id NOT IN (
        		SELECT cpt
        		FROM Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy cpt
         		WHERE cpt.lft <= :lft
        		AND cpt.root = :root
        	)
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('lft', $cpt->getLft());
        $query->setParameter('root',$cpt->getRoot());

        return $query->getResult();
    }

    public function getRootCpt($sortByField = NULL, $direction = 'asc')
    {
    	$dql = "
    	SELECT c.name as name , ch.id as id 
        FROM Claroline\CoreBundle\Entity\Competence\Competence c,
        	 Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy ch
        WHERE
        	c.id = ch.competence
        	AND ch.parent IS NULL
        ";
    	$query = $this->_em->createQuery($dql);

    	return $query->getResult();
    }

    public function findHiearchyNameById(CompetenceHierarchy $competence)
    {
        $dql = "
    	SELECT c.name as name , ch.id as id
        FROM Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy ch
        JOIN ch.competence c WHERE ch.root = :root
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('root',$competence->getRoot());

        return $query->getResult();
    }

    public function findHiearchyById(CompetenceHierarchy $competence)
    {
        $dql = "
    	SELECT c.name as name, c.description, c.score, ch.id as id
        FROM Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy ch
        JOIN ch.competence c WHERE ch.root = :root
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('root',$competence->getRoot());

        return $query->getResult();
    }

    public function findFullHiearchyById(array $roots)
    {
        $dql = "
        SELECT DISTINCT c
        FROM Claroline\CoreBundle\Entity\Competence\Competence c
        WHERE EXISTS
            (
                SELECT ch FROM Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy ch
                WHERE ch.root IN (:roots) AND ch.competence = c 
            )
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('roots', $roots);

        return $query->getResult();
    }
} 
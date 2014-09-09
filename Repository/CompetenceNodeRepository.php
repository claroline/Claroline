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

use Claroline\CoreBundle\Entity\Competence\Competence;
use Claroline\CoreBundle\Entity\Competence\CompetenceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CompetenceNodeRepository extends NestedTreeRepository
{
    public function findAdminRootCompetenceNodes()
    {
    	$dql = '
            SELECT cn
            FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode cn
            JOIN cn.competence c
            WHERE cn.parent IS NULL
            AND c.workspace IS NULL
            AND c.isPlatform = true
        ';
    	$query = $this->_em->createQuery($dql);

    	return $query->getResult();
    }

    public function findHierarchyByCompetenceNode(CompetenceNode $competenceNode)
    {
        $dql = '
            SELECT cn, c
            FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode cn
            JOIN cn.competence c
            WHERE cn.root = :root
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('root', $competenceNode->getRoot());

        return $query->getResult();
    }

    public function findCompetenceNodesByCompetence(Competence $competence)
    {
        $dql = '
            SELECT cn
            FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode cn
            WHERE cn.competence = :competence
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('competence', $competence);

        return $query->getResult();
    }

    public function findSubCompetenceNodesByCompetenceNode(CompetenceNode $node)
    {
        $dql = '
            SELECT cn
            FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode cn
            WHERE cn.root = :root
            AND cn.lft > :lft
            AND cn.rgt < :rgt
            ORDER BY cn.id DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('root', $node->getRoot());
        $query->setParameter('lft', $node->getLft());
        $query->setParameter('rgt', $node->getRgt());

        return $query->getResult();
    }

    public function findWorkspaceRootCompetenceNodes(Workspace $workspace)
    {
    	$dql = '
            SELECT cn, c
            FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode cn
            JOIN cn.competence c
            WHERE cn.parent IS NULL
            AND c.isPlatform = false
            AND c.workspace = :workspace
        ';
    	$query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

    	return $query->getResult();
    }

//    public function findHiearchyById(CompetenceNode $competenceNode)
//    {
//        $dql = "
//    	SELECT c.name as name, c.description, c.score as score,c.code as code, ch.id as id
//        FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode ch
//        JOIN ch.competence c WHERE ch.root = :root
//        ";
//        $query = $this->_em->createQuery($dql);
//        $query->setParameter('root',$competenceNode->getRoot());
//
//        return $query->getResult();
//    }
}

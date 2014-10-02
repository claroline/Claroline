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

use Claroline\CoreBundle\Entity\Competence\CompetenceNode;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\ORM\EntityRepository;

class CompetenceRepository extends EntityRepository
{
    public function findLinkableAdminCompetences(CompetenceNode $competenceNode)
    {
        $dql = '
            SELECT DISTINCT c
            FROM Claroline\CoreBundle\Entity\Competence\Competence c
            WHERE c.isPlatform = true
            AND c.workspace IS NULL
            AND NOT EXISTS (
                SELECT cn
                FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode cn
                WHERE cn.competence = c
                AND cn.root = :root
                AND (
                    (cn.lft <= :lft AND cn.rgt >= :rgt)
                    OR
                    (cn.lft >= :lft AND cn.rgt <= :rgt)
                )
            )
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('lft', $competenceNode->getLft());
        $query->setParameter('rgt', $competenceNode->getRgt());
        $query->setParameter('root', $competenceNode->getRoot());

        return $query->getResult();
    }

    public function findLinkableWorkspaceCompetences(
        Workspace $workspace,
        CompetenceNode $competenceNode
    )
    {
        $dql = '
            SELECT DISTINCT c
            FROM Claroline\CoreBundle\Entity\Competence\Competence c
            WHERE c.isPlatform = false
            AND c.workspace = :workspace
            AND NOT EXISTS (
                SELECT cn
                FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode cn
                WHERE cn.competence = c
                AND cn.root = :root
                AND (
                    (cn.lft <= :lft AND cn.rgt >= :rgt)
                    OR
                    (cn.lft >= :lft AND cn.rgt <= :rgt)
                )
            )
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);
        $query->setParameter('lft', $competenceNode->getLft());
        $query->setParameter('rgt', $competenceNode->getRgt());
        $query->setParameter('root', $competenceNode->getRoot());

        return $query->getResult();
    }

    public function findAdminCompetences($orderedBy = 'name', $order = 'ASC')
    {
        $dql = "
            SELECT c
            FROM Claroline\CoreBundle\Entity\Competence\Competence c
            WHERE c.isPlatform = true
            AND c.workspace IS NULL
            ORDER BY c.{$orderedBy} {$order}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findWorkspaceCompetences(
        Workspace $workspace,
        $orderedBy = 'name',
        $order = 'ASC'
    )
    {
        $dql = "
            SELECT c
            FROM Claroline\CoreBundle\Entity\Competence\Competence c
            WHERE c.isPlatform = false
            AND c.workspace = :workspace
            ORDER BY c.{$orderedBy} {$order}
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', $workspace);

        return $query->getResult();
    }

//    public function getRootCptWithWorkspace(Workspace $workspace)
//    {
//        $dql = "
//        SELECT DISTINCT c.name as name , ch.id as id
//        FROM Claroline\CoreBundle\Entity\Competence\Competence c,
//             Claroline\CoreBundle\Entity\Competence\CompetenceNode ch
//        WHERE ( c.workspace = :workspace OR c.workspace is NULL )
//        AND EXISTS
//            (
//                SELECT cpth FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode cpth
//                WHERE ch.parent IS NULL
//                )
//        AND c.id = ch.competence
//        ";
//        $query = $this->_em->createQuery($dql);
//        $query->setParameter('workspace', $workspace);
//
//        return $query->getResult();
//    }
//
//    public function findFullHiearchyById(array $roots)
//    {
//        $dql = "
//        SELECT DISTINCT c
//        FROM Claroline\CoreBundle\Entity\Competence\Competence c
//        WHERE EXISTS
//            (
//                SELECT ch FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode ch
//                WHERE ch.root IN (:roots) AND ch.competence = c
//            )
//        ";
//        $query = $this->_em->createQuery($dql);
//        $query->setParameter('roots', $roots);
//
//        return $query->getResult();
//    }
//
//    public function findCompetencesByWorkspace(Workspace $workspace)
//    {
//        $dql = '
//            SELECT c
//            FROM ClarolineCoreBundle:Competence\Competence c
//            WHERE c.workspace = :workspace
//        ';
//        $query = $this->_em->createQuery($dql);
//        $query->setParameter('workspace', $workspace);
//
//        return $query->getResult();
//    }
} 
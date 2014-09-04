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
use Claroline\CoreBundle\Entity\Competence\CompetenceNode;
use Claroline\CoreBundle\Entity\Competence\UserCompetence;
use Claroline\CoreBundle\Entity\Competence\Competence;


class UserCompetenceRepository extends EntityRepository
{
	public function findAll()
	{
		$dql = "
		SELECT cu, c, u FROM ClarolineCoreBundle:Competence\UserCompetence cu
		LEFT JOIN cu.competence c
		LEFT JOIN cu.user u
		ORDER BY cu.competence
		";
		$query = $this->_em->createQuery($dql);
		return $query->getResult();
	}

	public function findHiearchyByNode(CompetenceNode $competence)
	{
		$dql = "
        SELECT DISTINCT cu
        FROM ClarolineCoreBundle:Competence\UserCompetence cu
        JOIN cu.competence c
        WHERE EXISTS
            (
                SELECT ch FROM Claroline\CoreBundle\Entity\Competence\CompetenceNode ch
                WHERE ch.root = :root AND ch.competence = c AND ch.parent IS NULL
            )
		";
		$query = $this->_em->createQuery($dql);
		$query->setParameter('root',$competence->getRoot());
		return $query->getResult();
	}
	// get all the user who are in a learning outcomes 
	public function findByCompetence(Competence $competenceUser)
	{
		$dql="
			SELECT DISTINCT u FROM ClarolineCoreBundle:User u WHERE EXISTS (
				SELECT cu FROM ClarolineCoreBundle:Competence\UserCompetence cu
				WHERE cu.competence = :competence AND cu.user = u
			)
		";
		$query = $this->_em->createQuery($dql);
		$query->setParameter('competence', $competenceUser);
		return $query->getResult();
	}

	public function deleteNodeHiearchy(UserCompetence $userCompetence, CompetenceNode $root)
	{
		$dql="
		DELETE ClarolineCoreBundle:Competence\UserCompetence cu 
        WHERE cu.competence IN
            (
                SELECT DISTINCT c FROM ClarolineCoreBundle:Competence\Competence c
                WHERE EXISTS
                (
                	SELECT ch FROM ClarolineCoreBundle:Competence\CompetenceNode ch 
                	WHERE ch.competence = c 
                	AND ch.root = :root
                )
            )
		AND cu.user = :user
		";
		$query = $this->_em->createQuery($dql);
		$query->setParameter('user',$userCompetence->getUser());
		$query->setParameter('root',$root->getRoot());
		return $query->getResult();
	}

	public function findByWorkspace($workspace, $user)
	{
		$dql = "
		SELECT cu FROM ClarolineCoreBundle:Competence\UserCompetence cu
		WHERE  cu.user = :user 
		";
		$query = $this->_em->createQuery($dql);
		//$query->setParameter('workspace', $workspace);
		$query->setParameter('user', $user);

		return $query->getResult();
	}
}

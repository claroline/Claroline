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

class CompetenceRepository extends EntityRepository{

/*	public function findAll()
	{
		$dql = 'SELECT c
                FROM Claroline\CoreBundle\Entity\Competence\Competence c
                WHERE c.workspace = :workspace';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspace', false);

		return $query->getResult();

        return parent::findAll();
	} */

	public function getRoots()
	{
		$dql ='
			SELECT c FROM Claroline\CoreBundle\Entity\Competence\Competence c
			WHERE NOT EXISTS (
				SELECT ch FROM Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy ch 
				WHERE ch.competence = c
				)';
		$query = $this->_em->createQuery($dql);

		return $query->getResult();
	}

	public function getAllHierarchy()
	{
		$dql = '
			SELECT ch FROM Claroline\CoreBundle\Entity\Competence\CompetenceHierarchy ch
		';
		$query = $this->_em->createQuery($dql);

		return $query->getResult();
	}

    /**
     * @param Competence $competence
     * @param Competence $root
     *
     */
    public function getCompetencesNotParents(Competence $competence, Competence $root)
	{
    	
	}
} 
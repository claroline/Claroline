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
}

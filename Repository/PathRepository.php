<?php

namespace Innova\PathBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Innova\PathBundle\Entity\Path;
use Doctrine\ORM\EntityRepository;

class PathRepository extends EntityRepository
{

    public function findAllByWorkspaceByUser($workspace, $user)
    {

    	$dql = "SELECT p FROM Innova\PathBundle\Entity\Path p LEFT JOIN p.resourceNode rn WHERE rn.workspace = :workspace AND rn.creator = :user";

		$query = $this->_em->createQuery($dql)
				->setParameter('workspace', $workspace)
				->setParameter('user', $user);
		return $query->getResult();
    }

    public function findAllByWorkspaceByNotUser($workspace, $user)
    {
    	$dql = "SELECT p FROM Innova\PathBundle\Entity\Path p LEFT JOIN p.resourceNode rn WHERE rn.workspace = :workspace AND rn.creator != :user";

		$query = $this->_em->createQuery($dql)
				->setParameter('workspace', $workspace)
				->setParameter('user', $user);
		return $query->getResult();
    }
}

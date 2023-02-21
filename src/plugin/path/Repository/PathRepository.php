<?php

namespace Innova\PathBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Innova\PathBundle\Entity\Path\Path;

class PathRepository extends EntityRepository
{
    /**
     * Find all the Path using the $resourceNode as an overview resource or a primary resource of a Step.
     *
     * @return Path[]
     */
    public function findByPrimaryResource(ResourceNode $resourceNode)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT p
                FROM Innova\PathBundle\Entity\Path\Path AS p
                LEFT JOIN Innova\PathBundle\Entity\Step AS s WITH (s.path = p)
                LEFT JOIN s.resource AS n
                WHERE s.resource = :resourceNode 
                   OR p.overviewResource = :resourceNode
           ')
            ->setParameter('resourceNode', $resourceNode)
            ->getResult();
    }

    /**
     * Find user evaluations for the required resources embedded in a Path as an overview resource or a primary resource of a Step.
     * NB. This is used by the evaluation system of the Path, other embedded resources are not needed in this case.
     *
     * @return ResourceUserEvaluation[]
     */
    public function findRequiredEvaluations(Path $path, User $user)
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT e
                FROM Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation AS e
                LEFT JOIN e.resourceNode AS n
                LEFT JOIN Innova\PathBundle\Entity\Step AS s WITH (s.resource = n)
                LEFT JOIN Innova\PathBundle\Entity\Path\Path AS p WITH (p.overviewResource = n)
                WHERE n.required = 1
                  AND e.user = :user
                  AND (s.path = :pathResource OR p = :pathResource)
           ')
            ->setParameter('pathResource', $path)
            ->setParameter('user', $user)
            ->getResult();
    }
}

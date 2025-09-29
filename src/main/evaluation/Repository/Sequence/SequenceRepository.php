<?php

namespace Claroline\EvaluationBundle\Repository\Sequence;

use Claroline\AppBundle\Repository\UniqueValueFinder;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

class SequenceRepository extends EntityRepository
{
    use UniqueValueFinder;

    /**
     * Find all the Sequences using the $resourceNode as a primary resource of a Step.
     *
     * @return Sequence[]
     */
    public function findByResource(ResourceNode $resourceNode): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT p
                FROM Claroline\EvaluationBundle\Entity\Sequence\Sequence AS p
                LEFT JOIN Claroline\EvaluationBundle\Entity\Sequence\Step AS s WITH (s.path = p)
                LEFT JOIN s.resource AS n
                WHERE p.published = 1
                  AND p.archived = 0
                  AND s.resource = :resourceNode
           ')
            ->setParameter('resourceNode', $resourceNode)
            ->getResult();
    }

    public function findByResourceAndUser(ResourceNode $resourceNode, User $user): array
    {
        $roles = $user->getRoles();

        return $this->getEntityManager()
            ->createQuery('
                SELECT p
                FROM Claroline\EvaluationBundle\Entity\Sequence\Sequence AS p
                LEFT JOIN Claroline\EvaluationBundle\Entity\Sequence\Step AS s WITH (s.path = p)
                WHERE p.published = 1
                  AND p.archived = 0
                  AND s.resource = :resourceNode 
                  AND EXISTS (
                    SELECT a.id
                    FROM Claroline\EvaluationBundle\Entity\Sequence\Assignment AS a
                    LEFT JOIN a.role AS r
                    WHERE a.sequence = p
                      AND r.name IN (:roles)
                  )
           ')
            ->setParameter('resourceNode', $resourceNode)
            ->setParameter('roles', $roles)
            ->getResult();
    }

    public function isAssigned(Sequence $sequence, User $user): bool
    {
        $roles = $user->getRoles();

        return 0 < $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(s)
                FROM Claroline\EvaluationBundle\Entity\Sequence\Sequence AS s
                WHERE s = :sequence 
                  AND EXISTS (
                    SELECT a.id
                    FROM Claroline\EvaluationBundle\Entity\Sequence\Assignment AS a
                    LEFT JOIN Claroline\CoreBundle\Entity\Role AS r WITH (a.role = r)
                    WHERE a.sequence = :sequence
                      AND r.name IN (:roles)
                  )
           ')
            ->setParameter('sequence', $sequence)
            ->setParameter('roles', $roles)
            ->getSingleScalarResult();
    }

    public function isRequired(Sequence $sequence, User $user): bool
    {
        $roles = $user->getRoles();

        return 0 < $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(s)
                FROM Claroline\EvaluationBundle\Entity\Sequence\Sequence AS s
                WHERE s = :sequence 
                  AND EXISTS (
                    SELECT a.id
                    FROM Claroline\EvaluationBundle\Entity\Sequence\Assignment AS a
                    LEFT JOIN Claroline\CoreBundle\Entity\Role AS r WITH (a.role = r)
                    WHERE a.sequence = :sequence
                      AND r.name IN (:roles)
                      AND a.required = 1
                  )
           ')
            ->setParameter('sequence', $sequence)
            ->setParameter('roles', $roles)
            ->getSingleScalarResult();
    }

    /**
     * Find the users which are required to do the sequence.
     */
    public function findRequiredUserIds(Sequence $sequence): array
    {
        $results = $this->getEntityManager()
            ->createQuery('
                SELECT DISTINCT u.id
                FROM Claroline\CoreBundle\Entity\User AS u
                LEFT JOIN u.roles AS r
                LEFT JOIN u.groups AS g
                LEFT JOIN g.roles AS gr
                WHERE EXISTS (
                    SELECT a.id
                    FROM Claroline\EvaluationBundle\Entity\Sequence\Assignment AS a
                    WHERE a.sequence = :sequence
                      AND (a.role = r OR a.role = gr)
                      AND (a.required = 1 OR a.scored = 1)
                )
           ')
            ->setParameter('sequence', $sequence)
            ->setHydrationMode(AbstractQuery::HYDRATE_SINGLE_SCALAR)
            ->getResult();

        return array_map(function (array $result) {
            return $result['id'];
        }, $results);
    }
}

<?php

namespace Claroline\EvaluationBundle\Repository;

use Claroline\AppBundle\Repository\UniqueValueFinder;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
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
    public function findByRequiredResource(ResourceNode $resourceNode): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT p
                FROM Claroline\EvaluationBundle\Entity\Sequence\Sequence AS p
                LEFT JOIN Claroline\EvaluationBundle\Entity\Sequence\Step AS s WITH (s.path = p)
                LEFT JOIN s.resource AS n
                WHERE s.resource = :resourceNode 
                  AND s.required = true
           ')
            ->setParameter('resourceNode', $resourceNode)
            ->getResult();
    }

    public function findByRequiredResourceAndUser(ResourceNode $resourceNode, User $user): array
    {
        $roles = $user->getRoles();

        return $this->getEntityManager()
            ->createQuery('
                SELECT p
                FROM Claroline\EvaluationBundle\Entity\Sequence\Sequence AS p
                LEFT JOIN Claroline\EvaluationBundle\Entity\Sequence\Step AS s WITH (s.path = p)
                WHERE p.published = 1
                  AND s.resource = :resourceNode 
                  AND s.required = true
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

    /**
     * Find user evaluations for the resources embedded in a Sequence as a primary resource of a Step.
     * NB. This is used by the evaluation system of the Sequence, other embedded resources are not needed in this case.
     *
     * @return ResourceEvaluation[]
     */
    public function findResourceEvaluations(Sequence $sequence, User $user): array
    {
        return $this->getEntityManager()
            ->createQuery('
                SELECT e
                FROM Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation AS e
                LEFT JOIN e.resourceNode AS n
                LEFT JOIN Claroline\EvaluationBundle\Entity\Sequence\Step AS s WITH (s.resource = n)
                WHERE e.user = :user
                  AND s.path = :sequence
           ')
            ->setParameter('sequence', $sequence)
            ->setParameter('user', $user)
            ->getResult();
    }
}

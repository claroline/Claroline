<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Repository;

use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Doctrine\ORM\EntityRepository;

class ResourceAttemptRepository extends EntityRepository
{
    public function findOneInProgress(ResourceNode $node, User $user): ?ResourceEvaluation
    {
        return $this->createQueryBuilder('re')
            ->join('re.resourceUserEvaluation', 'rue')
            ->where('re.status IN (:status)')
            ->andWhere('rue.user = :user')
            ->andWhere('rue.resourceNode = :resourceNode')
            ->setParameter('status', [
                EvaluationStatus::NOT_ATTEMPTED,
                EvaluationStatus::TODO,
                EvaluationStatus::OPENED,
                EvaluationStatus::INCOMPLETE,
            ])
            ->setParameter('user', $user)
            ->setParameter('resourceNode', $node)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLast(ResourceNode $node, User $user): ?ResourceEvaluation
    {
        return $this->createQueryBuilder('re')
            ->join('re.resourceUserEvaluation', 'rue')
            ->andWhere('rue.user = :user')
            ->andWhere('rue.resourceNode = :resourceNode')
            ->orderBy('re.date', 'DESC')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->setParameter('resourceNode', $node)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findInProgress(ResourceNode $node)
    {
        return $this->createQueryBuilder('re')
            ->join('re.resourceUserEvaluation', 'rue')
            ->where('re.status IN (:status)')
            ->andWhere('rue.resourceNode = :resourceNode')
            ->setParameter('status', [
                EvaluationStatus::NOT_ATTEMPTED,
                EvaluationStatus::TODO,
                EvaluationStatus::OPENED,
                EvaluationStatus::INCOMPLETE,
            ])
            ->setParameter('resourceNode', $node)
            ->getQuery()
            ->getResult();
    }

    public function countTerminated(ResourceNode $resourceNode, User $user): int
    {
        return (int) $this->getEntityManager()
            ->createQuery('
                SELECT COUNT(a)
                FROM Claroline\CoreBundle\Entity\Resource\ResourceEvaluation AS a
                JOIN a.resourceUserEvaluation AS e
                WHERE e.user = :user
                  AND e.resourceNode = :resourceNode
                  AND a.status IN (:status)
            ')
            ->setParameters([
                'resourceNode' => $resourceNode,
                'user' => $user,
                'status' => [
                    EvaluationStatus::COMPLETED,
                    EvaluationStatus::PASSED,
                    EvaluationStatus::PARTICIPATED,
                    EvaluationStatus::FAILED,
                ],
            ])
            ->getSingleScalarResult();
    }
}

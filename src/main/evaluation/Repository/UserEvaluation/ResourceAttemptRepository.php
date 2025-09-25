<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Repository\UserEvaluation;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Doctrine\ORM\EntityRepository;

class ResourceAttemptRepository extends EntityRepository
{
    public function findOneInProgress(ResourceNode $node, User $user): ?ResourceAttempt
    {
        return $this->createQueryBuilder('re')
            ->join('re.resourceUserEvaluation', 'rue')
            ->where('re.status IN (:status)')
            ->andWhere('rue.user = :user')
            ->andWhere('rue.resourceNode = :resourceNode')
            ->andWhere('rue.archived = 0')
            ->setParameter('status', [
                EvaluationStatus::NOT_ATTEMPTED,
                EvaluationStatus::INCOMPLETE,
            ])
            ->setParameter('user', $user)
            ->setParameter('resourceNode', $node)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @deprecated
     */
    public function findLast(ResourceNode $node, User $user): ?ResourceAttempt
    {
        return $this->createQueryBuilder('re')
            ->join('re.resourceUserEvaluation', 'rue')
            ->andWhere('rue.user = :user')
            ->andWhere('rue.resourceNode = :resourceNode')
            ->andWhere('re.archived = 0')
            ->orderBy('re.date', 'DESC')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->setParameter('resourceNode', $node)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

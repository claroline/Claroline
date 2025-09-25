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
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Doctrine\ORM\EntityRepository;

class ResourceEvaluationRepository extends EntityRepository
{
    public function findInProgress(ResourceNode $resourceNode): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status IN (:status)')
            ->andWhere('e.workspace = :resourceNode')
            ->andWhere('e.archived = 0')
            ->setParameter('status', [
                EvaluationStatus::NOT_ATTEMPTED,
                EvaluationStatus::INCOMPLETE,
            ])
            ->setParameter('resourceNode', $resourceNode)
            ->getQuery()
            ->getResult();
    }
}

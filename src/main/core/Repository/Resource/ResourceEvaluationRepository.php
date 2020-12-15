<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Resource;

use Claroline\CoreBundle\Entity\Evaluation\AbstractEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class ResourceEvaluationRepository extends EntityRepository
{
    public function findOneInProgress(ResourceNode $node, User $user)
    {
        return $this->createQueryBuilder('re')
            ->join('re.resourceUserEvaluation', 'rue')
            ->where('re.status IN (:status)')
            ->andWhere('rue.user = :user')
            ->andWhere('rue.resourceNode = :resourceNode')
            ->setParameter('status', [
                AbstractEvaluation::STATUS_NOT_ATTEMPTED,
                AbstractEvaluation::STATUS_TODO,
                AbstractEvaluation::STATUS_OPENED,
                AbstractEvaluation::STATUS_INCOMPLETE,
            ])
            ->setParameter('user', $user)
            ->setParameter('resourceNode', $node)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

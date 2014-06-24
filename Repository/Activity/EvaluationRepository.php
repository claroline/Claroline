<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Activity;

use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class EvaluationRepository extends EntityRepository
{
    public function findEvaluationByUserAndActivityParams(
        User $user,
        ActivityParameters $activityParams,
        $executeQuery = true
    )
    {
        $dql = '
            SELECT e
            FROM Claroline\CoreBundle\Entity\Activity\Evaluation e
            WHERE e.user = :user
            AND e.activityParameters = :activityParameters
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('activityParameters', $activityParams);

        return $executeQuery ? $query->getOneOrNullResult(): $query;
    }
}

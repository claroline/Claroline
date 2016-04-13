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
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class PastEvaluationRepository extends EntityRepository
{
    public function findPastEvaluationsByUserAndActivityParams(
        User $user,
        ActivityParameters $activityParams,
        $executeQuery = true
    ) {
        $dql = '
            SELECT e
            FROM Claroline\CoreBundle\Entity\Activity\PastEvaluation e
            WHERE e.user = :user
            AND e.activityParameters = :activityParameters
            ORDER BY e.date DESC
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('activityParameters', $activityParams);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findPastEvaluationsByUserAndActivityParamsAndLog(
        User $user,
        ActivityParameters $activityParams,
        Log $log,
        $executeQuery = true
    ) {
        $dql = '
            SELECT e
            FROM Claroline\CoreBundle\Entity\Activity\PastEvaluation e
            WHERE e.user = :user
            AND e.activityParameters = :activityParameters
            AND e.log = :log
        ';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('activityParameters', $activityParams);
        $query->setParameter('log', $log);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}

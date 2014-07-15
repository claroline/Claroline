<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Badge;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Log\Log;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class BadgeRuleRepository extends EntityRepository
{
    /**
     * @param string $action
     * @param bool   $executeQuery
     *
     * @return array|\Doctrine\ORM\AbstractQuery
     */
    public function findBadgeAutomaticallyAwardedFromAction(Log $log, $executeQuery = true)
    {
        $actiontype = $log->getAction();

        if ($log->getResourceType()) {
            $actiontype = '[[' . $log->getResourceType()->getName() . ']]' . $log->getAction();
        }


        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b
                FROM ClarolineCoreBundle:Badge\Badge b
                JOIN b.badgeRules br
                WHERE (br.action = :action
                OR br.action = :action2)
                AND b.automaticAward = true'
            )
            ->setParameter('action', $log->getAction())
            ->setParameter('action2', $actiontype);

        return $executeQuery ? $query->getResult(): $query;
    }
}

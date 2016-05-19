<?php

namespace Icap\BadgeBundle\Repository;

use Icap\BadgeBundle\Entity\Badge;
use Claroline\CoreBundle\Entity\Log\Log;
use Doctrine\ORM\EntityRepository;

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
            $actiontype = '[['.$log->getResourceType()->getName().']]'.$log->getAction();
        }

        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT b
                FROM IcapBadgeBundle:Badge b
                JOIN b.badgeRules br
                WHERE (br.action = :action
                OR br.action = :action2)
                AND b.automaticAward = true'
            )
            ->setParameter('action', $log->getAction())
            ->setParameter('action2', $actiontype);

        return $executeQuery ? $query->getResult() : $query;
    }
}

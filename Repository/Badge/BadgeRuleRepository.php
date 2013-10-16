<?php

namespace Claroline\CoreBundle\Repository\Badge;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\User;
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
    public function findBadgeFromAction($action, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT b
                FROM ClarolineCoreBundle:Badge\Badge b
                JOIN b.badgeRules br
                WHERE br.action = :action'
            )
            ->setParameter('action', $action)
        ;

        return $executeQuery ? $query->getResult(): $query;
    }
}

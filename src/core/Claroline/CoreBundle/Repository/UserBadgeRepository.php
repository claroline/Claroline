<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Doctrine\ORM\EntityRepository;

class UserBadgeRepository extends EntityRepository
{
    /**
     * @param Badge $badge
     *
     * @return int
     */
    public function deleteByBadge(Badge $badge)
    {
        return $this->getEntityManager()
            ->createQuery('DELETE FROM CLaroline\CoreBundle\Entity\Badge\UserBadge ub where ub.badge = :badgeId')
            ->setParameter(':badgeId', $badge->getId())
            ->execute()
        ;
    }
}

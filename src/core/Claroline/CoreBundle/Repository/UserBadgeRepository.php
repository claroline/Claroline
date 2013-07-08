<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\User;
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

    /**
     * @param Badge $badge
     * @param User  $user
     *
     * @return int
     */
    public function deleteByBadgeAndUser(Badge $badge, User $user)
    {
        return $this->getEntityManager()
            ->createQuery('DELETE FROM CLaroline\CoreBundle\Entity\Badge\UserBadge ub where ub.badge = :badgeId AND ub.user = :userId')
            ->setParameter(':badgeId', $badge->getId())
            ->setParameter(':userId', $user->getId())
            ->execute()
        ;
    }
}

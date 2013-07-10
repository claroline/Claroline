<?php

namespace Claroline\BadgeBundle\Repository;

use Claroline\BadgeBundle\Entity\Badge;
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
            ->createQuery('DELETE FROM CLaroline\BadgeBundle\Entity\UserBadge ub where ub.badge = :badgeId')
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
            ->createQuery('DELETE FROM CLaroline\BadgeBundle\Entity\UserBadge ub where ub.badge = :badgeId AND ub.user = :userId')
            ->setParameter(':badgeId', $badge->getId())
            ->setParameter(':userId', $user->getId())
            ->execute()
        ;
    }
}

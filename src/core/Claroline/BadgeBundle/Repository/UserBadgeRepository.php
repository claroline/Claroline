<?php

namespace Claroline\BadgeBundle\Repository;

use Claroline\BadgeBundle\Entity\Badge;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserBadgeRepository extends EntityRepository
{
    /**
     * @param Badge $badge
     * @param User  $user
     *
     * @return \Claroline\BadgeBundle\Entity\UserBadge|null
     */
    public function findOneByBadgeAndUser(Badge $badge, User $user)
    {
        return $this->findOneBy(array('badge' => $badge, 'user' => $user));
    }
}

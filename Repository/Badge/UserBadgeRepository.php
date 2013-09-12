<?php

namespace Claroline\CoreBundle\Repository\Badge;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserBadgeRepository extends EntityRepository
{
    /**
     * @param Badge $badge
     * @param User  $user
     *
     * @return \Claroline\CoreBundle\Entity\Badge\UserBadge|null
     */
    public function findOneByBadgeAndUser(Badge $badge, User $user)
    {
        return $this->findOneBy(array('badge' => $badge, 'user' => $user));
    }
}

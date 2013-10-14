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

    /**
     * @param User $user
     *
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findByUser(User $user, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery('
                SELECT ub, b
                FROM ClarolineCoreBundle:Badge\UserBadge ub
                JOIN ub.badge b
                WHERE ub.user = :userId'
            )
            ->setParameter('userId', $user->getId())
        ;

        return $executeQuery ? $query->getResult(): $query;
    }
}

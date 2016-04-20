<?php

namespace Icap\BadgeBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class BadgeCollectionRepository extends EntityRepository
{
    /**
     * @param User $user
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findByUser(User $user, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT badgeCollection, userBadges
                FROM IcapBadgeBundle:BadgeCollection badgeCollection
                LEFT JOIN badgeCollection.userBadges userBadges
                WHERE badgeCollection.user = :userId
                ORDER BY badgeCollection.name ASC'
            )
            ->setParameter('userId', $user->getId());

        return $executeQuery ? $query->getResult() : $query;
    }
}

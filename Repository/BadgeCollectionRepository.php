<?php

namespace Icap\BadgeBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class BadgeCollectionRepository extends EntityRepository
{
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
            ->createQuery(
                'SELECT bc, b
                FROM ClarolineCoreBundle:Badge\BadgeCollection bc
                LEFT JOIN bc.badges b
                WHERE bc.user = :userId
                ORDER BY bc.name ASC'
            )
            ->setParameter('userId', $user->getId());

        return $executeQuery ? $query->getResult(): $query;
    }
}

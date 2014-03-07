<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Badge;

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

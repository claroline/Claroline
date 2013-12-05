<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Oauth;

use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class ClientRepository extends EntityRepository
{
    /**
     * @param User $user
     *
     * @param bool $executeQuery
     *
     * @return Query|array
     */
    public function findByUserWithAuthCode(User $user, $executeQuery = true)
    {
        $query = $this->getEntityManager()
            ->createQuery(
                'SELECT c, ac
                FROM ClarolineCoreBundle:Oauth\AuthCode ac
                JOIN ac.client c
                WHERE ac.user = :userId
            ')
            ->setParameter('userId', $user->getId());

        return $executeQuery ? $query->getResult(): $query;
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RemoteUserSynchronizationBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class RemoteUserTokenRepository extends EntityRepository
{
    public function findActivatedRemoteUserTokenByUserAndToken(User $user, $token, $date)
    {
        $dql = '
            SELECT ut
            FROM Claroline\RemoteUserSynchronizationBundle\Entity\RemoteUserToken ut
            WHERE ut.user = :user
            AND ut.token = :token
            AND ut.activated = true
            AND ut.expirationDate > :date
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('token', $token);
        $query->setParameter('date', $date);

        return $query->getOneOrNullResult();
    }
}

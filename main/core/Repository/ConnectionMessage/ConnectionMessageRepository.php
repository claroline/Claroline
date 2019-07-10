<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\ConnectionMessage;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class ConnectionMessageRepository extends EntityRepository
{
    public function findConnectionMessageByUser(User $user)
    {
        $dql = '
            SELECT DISTINCT m
            FROM Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage m
            JOIN m.roles r
            WHERE m.accessibleFrom <= :now
            AND m.accessibleUntil >= :now
            AND r.name IN (:roles)
            AND NOT EXISTS (
                SELECT cm
                FROM Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage cm
                JOIN cm.users cmu
                WHERE m = cm
                AND cmu = :user
            )
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('now', new \DateTime());
        $query->setParameter('user', $user);
        $query->setParameter('roles', $user->getRoles());

        return $query->getResult();
    }
}

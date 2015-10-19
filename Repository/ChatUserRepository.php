<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class ChatUserRepository extends EntityRepository
{
    public function findChatUserByUser(User $user)
    {
        $dql = '
            SELECT cu
            FROM Claroline\ChatBundle\Entity\ChatUser cu
            WHERE cu.user = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $query->getOneOrNullResult();
    }
}

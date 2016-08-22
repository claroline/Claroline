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

    public function findChatUsers($search = '', $orderedBy = 'username', $order = 'ASC')
    {
        $dql = "
            SELECT cu
            FROM Claroline\ChatBundle\Entity\ChatUser cu
            JOIN cu.user u
            WHERE UPPER(u.firstName) LIKE :search
            OR UPPER(u.lastName) LIKE :search
            OR CONCAT(UPPER(u.firstName), CONCAT(' ', UPPER(u.lastName))) LIKE :search
            OR CONCAT(UPPER(u.lastName), CONCAT(' ', UPPER(u.firstName))) LIKE :search
            OR UPPER(u.username) LIKE :search
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findChatUsersByUsernames(array $usernames)
    {
        $dql = '
            SELECT cu
            FROM Claroline\ChatBundle\Entity\ChatUser cu
            WHERE cu.chatUsername IN (:usernames)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('usernames', $usernames);

        return $query->getResult();
    }
}

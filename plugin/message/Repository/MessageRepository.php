<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Claroline\CoreBundle\Entity\User;
use Claroline\MessageBundle\Entity\Message;

class MessageRepository extends NestedTreeRepository
{
    /**
     * Returns the ancestors of a message (the message itself is also returned).
     *
     * @param Message $message
     *
     * @return array[Message]
     */
    public function findAncestors(Message $message, User $user)
    {
        $dql = "
            SELECT m
            FROM Claroline\MessageBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user u
            WHERE m.lft BETWEEN m.lft AND m.rgt
            AND m.root = {$message->getRoot()}
            AND m.lvl <= {$message->getLvl()}
            AND (
                u.id = :userid
                OR m.user = :userid
            )
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('userid', $user->getId());

        return $query->getResult();
    }

    /**
     * Counts the number of unread messages of a user.
     *
     * @param User $user
     *
     * @return int
     */
    public function countUnread(User $user)
    {
        return $this->createQueryBuilder('m')
            ->select('count(m)')
            ->join('m.userMessages', 'um')
            ->where('m.user != :sender')
            ->orWhere('m.user IS NULL')
            ->andWhere('um.user = :user')
            ->andWhere('um.isRead = 0')
            ->andWhere('um.isRemoved = 0')
            ->setParameter(':user', $user)
            ->setParameter(':sender', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

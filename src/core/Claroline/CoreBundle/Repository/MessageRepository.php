<?php

namespace Claroline\CoreBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\User;

class MessageRepository extends NestedTreeRepository
{
    /**
     * Returns the ancestors of a message (the message itself is also returned).
     *
     * @param Message $message
     *
     * @return array[Message]
     */
    public function findAncestors(Message $message)
    {
        $dql = "
            SELECT m FROM Claroline\CoreBundle\Entity\Message m
            WHERE m.lft BETWEEN m.lft AND m.rgt
            AND m.root = {$message->getRoot()}
            AND m.lvl <= {$message->getLvl()}
        ";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Counts the number of unread messages of a user.
     *
     * @param User $user
     *
     * @return integer
     */
    public function countUnread(User $user)
    {
        $dql = "
            SELECT COUNT(m) FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user u
            JOIN m.user creator
            WHERE u.id = {$user->getId()}
            AND um.isRead = false
            AND um.isRemoved = false
            AND creator.id != {$user->getId()}
        ";

        $query = $this->_em->createQuery($dql);
        $result = $query->getArrayResult();

        //?? getFirstResult and aliases do not work. Why ?
        return $result[0][1];
    }
}

<?php

namespace Claroline\CoreBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Claroline\CoreBundle\Entity\Message;
use Claroline\CoreBundle\Entity\User;

class MessageRepository extends NestedTreeRepository
{
    /**
     * Returns the ancestors of a message.
     *
     * @param Message $message
     *
     * @return type
     */
    public function findAncestors(Message $message)
    {
        $dql = "SELECT m FROM Claroline\CoreBundle\Entity\Message m
            WHERE m.lft BETWEEN m.lft AND m.rgt
            AND m.root = {$message->getRoot()}
            AND m.lvl <= {$message->getLvl()}";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Count the number of unread messages for a user.
     *
     * @param User $user
     *
     * @return integer
     */
    public function countUnread(User $user)
    {
        $dql = "SELECT Count(m) FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user u
            WHERE u.id = {$user->getId()}
            AND um.isRead = false
            AND um.isRemoved = false"
           ;

        $query = $this->_em->createQuery($dql);
        $result = $query->getArrayResult();

        //?? getFirstResult and alisases do not work. Why ?
        return $result[0][1];
    }

    /**
     * Warning. Returns UserMessage entities (the entities from the join table).
     *
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param boolean $isRemoved
     * @param boolean $getQuery
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findReceivedByUser(User $user, $isRemoved = false, $getQuery = false)
    {
        $isRemovedText = ($isRemoved) ? 'true' : 'false';
        $dql = "SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemovedText}
            AND um.isSent = false
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);

        return ($getQuery) ? $query: $query->getResult();

    }

    public function findSentByUser(User $user, $isRemoved = false, $getQuery = false)
    {
        $isRemovedText = ($isRemoved) ? 'true' : 'false';
        $dql = "SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemovedText}
            AND um.isSent = true
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);

        return ($getQuery) ? $query: $query->getResult();
    }

    /**
     * Warning. Returns UserMessage entities (the entities from the join table).
     *
     * @param string $search
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param boolean $isRemoved
     * @param boolean $getQuery
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findReceivedByUserAndObjectAndUsername(
        User $user,
        $search,
        $isRemoved = false,
        $getQuery = false
    )
    {
        $isRemovedText = ($isRemoved) ? 'true' : 'false';
        $upperSearch = strtoupper($search);

        $dql = "SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemovedText}
            AND um.isSent = false
            AND UPPER(m.object) LIKE :search
            OR UPPER(m.senderUsername) LIKE :search
            AND um.isRemoved = {$isRemovedText}
            AND um.isSent = false
            AND u.id = {$user->getId()}
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$upperSearch}%");

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findSentByUserAndObjectAndUsername(
        User $user,
        $search,
        $isRemoved = false,
        $getQuery = false
    )
    {
        $isRemovedText = ($isRemoved) ? 'true' : 'false';
        $search = strtoupper($search);
        $dql = "SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemovedText}
            AND um.isSent = true
            AND UPPER (m.object) LIKE :search
            OR u.id = {$user->getId()}
            AND um.isRemoved = {$isRemovedText}
            AND um.isSent = true
            AND UPPER (m.receiverUsername) LIKE :search
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findRemovedByUser(User $user, $getQuery = false)
    {
        $dql = "SELECT um, u, m FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = true
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);

        return ($getQuery) ? $query: $query->getResult();
    }

    /**
     * Warning. Returns UserMessage entities (the entities from the join table).
     *
     * @param string $search
     * @param \Claroline\CoreBundle\Entity\User $user
     * @param boolean $getQuery
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findRemovedByUserAndObjectAndUsername(User $user, $search, $getQuery = false)
    {
        $search = strtoupper($search);

        $dql = "SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = true
            AND UPPER(m.object) LIKE :search
            OR um.isRemoved = true
            AND u.id = {$user->getId()}
            AND UPPER(m.senderUsername) LIKE :search
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");

        return ($getQuery) ? $query: $query->getResult();
    }
}
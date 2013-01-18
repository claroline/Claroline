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
    public function getAncestors(Message $message)
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
    public function countUnreadMessage(User $user)
    {
        $dql = "SELECT Count(m) FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user u
            WHERE u.id = {$user->getId()}
            AND um.isRead = 0
            AND um.isRemoved = 0"
           ;

        $query = $this->_em->createQuery($dql);
        $result = $query->getArrayResult();

        //?? getFirstResult and alisases do not work. Why ?
        return $result[0][1];
    }

    public function getUserReceivedMessages($user, $isRemoved = false, $offset = null, $limit = null)
    {
        $isRemoved = ($isRemoved) ? 1: 0;
        $dql = "SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemoved}
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function getSentMessages($user, $isRemoved = false, $offset = null, $limit = null)
    {
        $isRemoved = ($isRemoved) ? 1: 0;
        $dql = "SELECT m, u, um, umu FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user umu
            JOIN m.user u
            WHERE u.id = {$user->getId()}
            AND m.isRemoved = {$isRemoved}
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchUserReceivedMessages($search, $user, $isRemoved = false, $offset = null, $limit = null)
    {
        $search = strtoupper($search);
        $isRemoved = ($isRemoved) ? 1: 0;
        $dql = "SELECT um, m, u, mu FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            JOIN m.user mu
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemoved}
            AND UPPER(m.object) LIKE :search
            OR UPPER(mu.username) LIKE :search
            AND um.isRemoved = {$isRemoved}
            AND u.id = {$user->getId()}
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchSentMessages($search, $user, $isRemoved = false, $offset = null, $limit = null)
    {
        $isRemoved = ($isRemoved) ? 1: 0;
        $search = strtoupper($search);
        $dql = "SELECT m, u, um, umu FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user umu
            JOIN m.user u
            WHERE u.id = {$user->getId()}
            AND m.isRemoved = {$isRemoved}
            AND UPPER (m.object) LIKE :search
            OR UPPER (umu.username) LIKE :search
            AND u.id = {$user->getId()}
            AND m.isRemoved = {$isRemoved}
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('search', "%{$search}%");
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function getRemovedMessages($user, $offset = null, $limit = null)
    {
        $dql = "SELECT um, m, u, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            JOIN m.user mu
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = 1
            OR mu.id = {$user->getId()}
            AND m.isRemoved = 1
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function searchRemovedMessages($search, $user, $offset = null, $limit = null)
    {
        $search = strtoupper($search);

        $dql = "SELECT um, m, u, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            JOIN m.user mu
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = 1
            AND UPPER(m.object) LIKE :search
            OR mu.id = {$user->getId()}
            AND m.isRemoved = 1
            AND UPPER(m.object) LIKE :search
            OR m.isRemoved = 1
            AND UPPER(mu.username) LIKE :search
            OR um.isRemoved = 1
            AND u.id = {$user->getId()}
            AND UPPER(mu.username) LIKE :search
            ORDER BY m.date DESC";

        $query = $this->_em->createQuery($dql);
        $query->setFirstResult($offset)
            ->setMaxResults($limit);
        $query->setParameter('search', "%{$search}%");
        $paginator = new Paginator($query, true);

        return $paginator;
    }
}

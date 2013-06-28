<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;

class UserMessageRepository extends EntityRepository
{


    public function findReceivedByUser(User $user, $executeQuery = true)
    {
        $dql = "
            SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = false
            AND um.isSent = false
            ORDER BY m.date DESC
        ";
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     *
     *
     * @param User      $user
     * @param boolean   $executeQuery
     *
     * @return array[UserMessage]|Query
     */
    public function findSentByUser(User $user, $executeQuery = true)
    {
        $dql = "
            SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = false
            AND um.isSent = true
            ORDER BY m.date DESC
        ";
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Returns UserMessage received by a user, filtered by a search
     * on the object or on the username of the sender.
     *
     * @param User      $receiver
     * @param string    $objectOrSenderUsernameSearch
     * @param boolean   $executeQuery
     *
     * @return array[UserMessage]|Query
     */
    public function findReceivedByObjectOrSender(
        User $receiver,
        $objectOrSenderUsernameSearch,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$receiver->getId()}
            AND um.isRemoved = false
            AND um.isSent = false
            AND (
                UPPER(m.object) LIKE :search
                OR UPPER(m.senderUsername) LIKE :search
            )
            ORDER BY m.date DESC
        ";
        $query = $this->_em->createQuery($dql);
        $searchParameter = '%' . strtoupper($objectOrSenderUsernameSearch) . '%';
        $query->setParameter('search', $searchParameter);

        return $executeQuery ? $query->getResult() : $query;
    }

    // TODO refactor with previous query
    // does that make sense to search on the sender ???
    public function findSentByObjectOrSender(
        User $sender,
        $objectOrSenderUsernameSearch,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$sender->getId()}
            AND um.isRemoved = false
            AND um.isSent = true
            AND (
                UPPER(m.object) LIKE :search
                OR UPPER(m.senderUsername) LIKE :search
            )
            ORDER BY m.date DESC
        ";
        $query = $this->_em->createQuery($dql);
        $searchParameter = '%' . strtoupper($objectOrSenderUsernameSearch) . '%';
        $query->setParameter('search', $searchParameter);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findRemovedByUser(User $user, $executeQuery = true)
    {
        $dql = "SELECT um, u, m FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = true
            ORDER BY m.date DESC";

        return $executeQuery ? $query->getResult() : $query;
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
    public function findRemovedByUserAndObjectAndUsername(User $user, $search, $executeQuery = true)
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

    public function findUserMessages(User $user, array $messages)
    {
        $firstMsg = array_pop($messages);

        $dql = "SELECT um FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE m.id IN ({$firstMsg->getId()}";


        foreach ($messages as $message) {
            $dql .= ", {$message->getId()}";
        }

        $dql .= ") AND u.id = {$user->getId()}";
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

}
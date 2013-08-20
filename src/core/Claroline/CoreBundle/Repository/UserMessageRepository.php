<?php

namespace Claroline\CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\CoreBundle\Entity\User;

class UserMessageRepository extends EntityRepository
{
    /**
     * Finds UserMessage marked as sent by a user.
     *
     * @param User    $user
     * @param boolean $executeQuery
     *
     * @return array[UserMessage]|Query
     */
    public function findSent(User $user, $executeQuery = true)
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
     * Finds UserMessage received by a user.
     *
     * @param User    $user
     * @param boolean $executeQuery
     *
     * @return array[UserMessage]|Query
     */
    public function findReceived(User $user, $executeQuery = true)
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
     * Finds UserMessage removed by a user.
     *
     * @param User    $user
     * @param boolean $executeQuery
     *
     * @return array[UserMessage]|Query
     */
    public function findRemoved(User $user, $executeQuery = true)
    {
        $dql = "
            SELECT um, u, m FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = true
            ORDER BY m.date DESC
        ";
        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Finds UserMessage received by a user, filtered by a search
     * on the object or on the username of the sender.
     *
     * @param User    $receiver
     * @param string  $objectOrSenderUsernameSearch
     * @param boolean $executeQuery
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

    /**
     * Finds UserMessage sent by a user, filtered by a search on the object.
     *
     * @param User    $sender
     * @param string  $objectSearch
     * @param boolean $executeQuery
     *
     * @return array[UserMessage]|Query
     */
    public function findSentByObject(User $sender, $objectSearch, $executeQuery = true)
    {
        $dql = "
            SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$sender->getId()}
            AND um.isRemoved = false
            AND um.isSent = true
            AND UPPER(m.object) LIKE :search
            ORDER BY m.date DESC
        ";
        $query = $this->_em->createQuery($dql);
        $searchParameter = '%' . strtoupper($objectSearch) . '%';
        $query->setParameter('search', $searchParameter);

        return $executeQuery ? $query->getResult() : $query;
    }

    /**
     * Finds UserMessage removed by a user, filtered by a search
     * on the object or on the username of the sender.
     *
     * @param User    $user
     * @param string  $objectOrSenderUsernameSearch
     * @param boolean $executeQuery
     *
     * @return array[UserMessage]|Query
     */
    public function findRemovedByObjectOrSender(
        User $user,
        $objectOrSenderUsernameSearch,
        $executeQuery = true
    )
    {
        $dql = "
            SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = true
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

    /**
     * Finds UserMessage received or sent by a user, filtered by specific messages.
     *
     * @param User           $user
     * @param array[Message] $messages
     *
     * @return array[UserMessage]
     */
    public function findByMessages(User $user, array $messages)
    {
        $messageIds = array();

        foreach ($messages as $message) {
            $messageIds[] = $message->getId();
        }

        $dql = '
            SELECT um FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE m.id IN (:messageIds)
            AND u.id = :userId
            ORDER BY m.date DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('messageIds', $messageIds);
        $query->setParameter('userId', $user->getId());

        return $query->getResult();
    }
}

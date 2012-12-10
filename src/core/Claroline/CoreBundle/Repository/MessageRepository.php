<?php

namespace Claroline\CoreBundle\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class MessageRepository extends NestedTreeRepository
{
    public function getAncestors($message)
    {
        $dql = "SELECT m FROM Claroline\CoreBundle\Entity\Message m
            WHERE m.lft BETWEEN m.lft AND m.rgt
            AND m.root = {$message->getRoot()}
            AND m.lvl <= {$message->getLvl()}";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getUnreadMessages($user)
    {
        $dql = "SELECT m FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user u
            WHERE u.id = {$user->getId()}
            AND um.isRead = 0
            AND um.isRemoved = 0"
           ;

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getReadMessages($user)
    {
        $dql = "SELECT m FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.fromUser u
            WHERE u.id = {$user->getId()}";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getUserReceivedMessages($user, $isRemoved = false)
    {
        ($isRemoved) ? $isRemoved = 1: $isRemoved = 0;
        $dql = "SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemoved}";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function getSentMessages($user, $isRemoved = false)
    {
        ($isRemoved) ? $isRemoved = 1: $isRemoved = 0;
        $dql = "SELECT m, u, um, umu FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user umu
            JOIN m.user u
            WHERE u.id = {$user->getId()}
            AND m.isRemoved = {$isRemoved}";

        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function searchUserReceivedMessagesObject($object, $user, $isRemoved = false)
    {
        ($isRemoved) ? $isRemoved = 1: $isRemoved = 0;
        $dql = "SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemoved}
            AND m.object LIKE :object";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('object',  "%{$object}%");

        return $query->getResult();
    }

    public function searchSentMessagesObject($object, $user, $isRemoved = false)
    {
        ($isRemoved) ? $isRemoved = 1: $isRemoved = 0;
        $dql = "SELECT m, u, um, umu FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user umu
            JOIN m.user u
            WHERE u.id = {$user->getId()}
            AND m.isRemoved = {$isRemoved}
            AND m.object LIKE :object";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('object',  "%{$object}%");

        return $query->getResult();
    }

    public function searchFromUser($fromUser, $user, $isRemoved = false)
    {
        ($isRemoved) ? $isRemoved = 1: $isRemoved = 0;
        $dql = "SELECT um, m, u FROM Claroline\CoreBundle\Entity\UserMessage um
            JOIN um.user u
            JOIN um.message m
            JOIN m.user mu
            WHERE u.id = {$user->getId()}
            AND um.isRemoved = {$isRemoved}
            AND mu.username LIKE :fromUser";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('fromUser',  "%{$fromUser}%");

        return $query->getResult();
    }

    public function searchToUser($toUser, $user, $isRemoved = false)
    {
        ($isRemoved) ? $isRemoved = 1: $isRemoved = 0;
        $dql = "SELECT m, u, um, umu FROM Claroline\CoreBundle\Entity\Message m
            JOIN m.userMessages um
            JOIN um.user umu
            JOIN m.user u
            WHERE u.id = {$user->getId()}
            AND m.isRemoved = {$isRemoved}
            AND umu.username LIKE :toUser";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('toUser',  "%{$toUser}%");

        return $query->getResult();
    }

}

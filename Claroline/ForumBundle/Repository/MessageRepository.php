<?php

namespace Claroline\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class MessageRepository extends EntityRepository
{
    public function getMessages($subject, $offset = null, $limit = null)
    {
        $dql = "
            SELECT m, u, ws FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.creator u
            JOIN u.personalWorkspace ws
            JOIN m.parent pri
            WHERE pri.id = {$subject->getId()}";

        $query = $this->_em->createQuery($dql);

        $query->getResult();
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);

        return $query->getResult();
    }
}
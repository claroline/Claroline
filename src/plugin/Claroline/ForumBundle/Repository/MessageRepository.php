<?php

namespace Claroline\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class MessageRepository extends EntityRepository
{
    public function getMessages($subjectInstance, $offset = null, $limit = null)
    {
        $dql = "
            SELECT m, u, ws FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.resourceInstances ri
            JOIN m.creator u
            JOIN u.personalWorkspace ws
            JOIN ri.parent pri
            WHERE pri.id = {$subjectInstance->getId()}";

        $query = $this->_em->createQuery($dql);

        $query->getResult();
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);

        return $query->getResult();
    }
}
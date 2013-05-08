<?php

namespace Claroline\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Claroline\ForumBundle\Entity\Subject;

class MessageRepository extends EntityRepository
{
    public function findBySubject($subject, $getQuery = false)
    {
        $dql = "
            SELECT m, u FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.creator u
            JOIN m.subject subject
            WHERE subject.id = {$subject->getId()}";

        $query = $this->_em->createQuery($dql);

        return ($getQuery) ? $query: $query->getResult();
    }

    public function findInitialBySubject($subjectId)
    {
        $dql = "SELECT m FROM  Claroline\ForumBundle\Entity\Message m
                WHERE m.id IN (SELECT min(m_1.id) FROM  Claroline\ForumBundle\Entity\Message m_1
                    JOIN m_1.subject s_2
                    WHERE s_2 = {$subjectId})
                ";

        $query = $this->_em->createQuery($dql);

        return $query->getSingleResult();
    }
}
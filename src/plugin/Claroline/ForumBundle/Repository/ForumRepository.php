<?php

namespace Claroline\ForumBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ForumRepository extends EntityRepository
{
      const SELECT_SUBJECT = "
            ri.id as instanceId,
            COUNT(m_count.id) AS count_messages,
            MAX(m.created) AS last_message_created,
            s.id as subjectId,
            s.created as subject_created,
            ri.name as title,
            subjectCreator.lastName as subject_creator_lastname,
            subjectCreator.firstName as subject_creator_firstname,
            lastUser.lastName as last_message_creator_lastname,
            lastUser.firstName as last_message_creator_firstname
            ";

      /**
       * Deep magic goes here.
       * Gets a subject with some of its last messages datas.
       *
       * @param ResourceInstance $forumInstance
       * @return type
       */
    public function getSubjects($forumInstance, $offset = null, $limit = null)
    {
        $dql = "
        SELECT ".self::SELECT_SUBJECT."
        FROM  Claroline\ForumBundle\Entity\Subject s
        JOIN s.messages m
        JOIN s.messages m_count
        JOIN s.resourceInstances ri
        JOIN s.creator subjectCreator
        JOIN ri.parent pri
        JOIN m.creator lastUser WITH lastUser.id =
            (
                SELECT lcu.id FROM Claroline\ForumBundle\Entity\Message m2
                JOIN m2.subject s2
                JOIN m2.creator lcu
                JOIN s2.forum f2
                JOIN f2.resourceInstances ri2
                WHERE NOT EXISTS
                (
                    SELECT m3 FROM Claroline\ForumBundle\Entity\Message m3
                    JOIN m3.subject s3
                    WHERE s2.id = s3.id
                    AND m2.id < m3.id
                )
                and ri2.id = :instanceId
                and m2.id = m.id
            )
        WHERE   pri.id = :instanceId
        GROUP BY s.id
        ORDER BY count_messages DESC";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('instanceId', $forumInstance->getId());
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);

        return $query->getArrayResult();
    }

    public function getMessages($subjectInstance, $offset, $limit)
    {
        $dql = "
            SELECT m FROM Claroline\ForumBundle\Entity\Messages m
            JOIN m.resourceInstances ri
            JOIN ri.parent pri
            WHERE pri.id = :instanceId
            ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('instanceId', $subjectInstance->getId());
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);

        return $query->getResults();
    }

    public function countMessagesForSubjectInstance($subjectInstance)
    {
        $dql = "
            SELECT Count(m) FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.resourceInstances ri
            JOIN ri.parent pri
            WHERE pri.id = :instanceId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('instanceId', $subjectInstance->getId());

        return $query->getSingleScalarResult();
    }

    public function countSubjectsFormForumInstance($forumInstance)
    {
        $dql = "
            SELECT COUNT(s) FROM Claroline\ForumBundle\Entity\Subject s
            JOIN s.resourceInstances ri
            JOIN ri.parent pri
            WHERE pri.id = :instanceId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('instanceId', $forumInstance->getId());

        return $query->getSingleScalarResult();
    }
}

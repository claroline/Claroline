<?php

namespace Claroline\ForumBundle\Repository;

use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Doctrine\ORM\EntityRepository;

class ForumRepository extends EntityRepository
{

      /**
       * Deep magic goes here.
       * Gets a subject with some of its last messages datas.
       *
       * @param ResourceInstance $forum
       * @return type
       */
    public function findSubjects(Forum $forum, $offset = null, $limit = null)
    {
        $dql = "
        SELECT
        s.id as id,
        COUNT(m_count.id) AS count_messages,
        MAX(m.creationDate) AS last_message_created,
        s.id as subjectId,
        s.creationDate as subject_created,
        s.title as title,
        subjectCreator.lastName as subject_creator_lastname,
        subjectCreator.firstName as subject_creator_firstname,
        lastUser.lastName as last_message_creator_lastname,
        lastUser.firstName as last_message_creator_firstname

        FROM  Claroline\ForumBundle\Entity\Subject s

        JOIN s.messages m
        JOIN s.messages m_count
        JOIN s.creator subjectCreator
        JOIN s.forum forum
        JOIN m.creator lastUser WITH lastUser.id =
            (
                SELECT lcu.id FROM Claroline\ForumBundle\Entity\Message m2
                JOIN m2.subject s2
                JOIN m2.creator lcu
                JOIN s2.forum f2
                WHERE NOT EXISTS
                (
                    SELECT m3 FROM Claroline\ForumBundle\Entity\Message m3
                    JOIN m3.subject s3
                    WHERE s2.id = s3.id
                    AND m2.id < m3.id
                )
                and f2.id = :forumId
                and m2.id = m.id
            )
        WHERE   forum.id = :forumId
        GROUP BY s.id
        ORDER BY count_messages DESC";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('forumId', $forum->getId());
        $query->setMaxResults($limit);
        $query->setFirstResult($offset);

        return $query->getArrayResult();
    }

    public function countMessagesForSubject(Subject $subject)
    {
        $dql = "
            SELECT Count(m) FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.subject s
            WHERE s.id = :subjectId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('subjectId', $subject->getId());

        return $query->getSingleScalarResult();
    }

    public function countSubjectsForForum($forum)
    {
        $dql = "
            SELECT COUNT(s) FROM Claroline\ForumBundle\Entity\Subject s
            JOIN s.forum p
            WHERE p.id = :forumId";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('forumId', $forum->getId());

        return $query->getSingleScalarResult();
    }
}

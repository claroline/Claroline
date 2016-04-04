<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Claroline\ForumBundle\Entity\Subject;

class MessageRepository extends EntityRepository
{
    public function findBySubject(Subject $subject, $getQuery = false)
    {
        $dql = "
            SELECT m, u, pws FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.creator u
            LEFT JOIN u.personalWorkspace pws
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
    public function findNLastByForum(array $workspaces, array $roles, $n, User $user = null)
    {
        $dql = "SELECT m FROM Claroline\ForumBundle\Entity\Message m
                JOIN m.subject s
                JOIN s.category c
                JOIN c.forum as f
                JOIN f.resourceNode n
                JOIN n.workspace w
                JOIN n.rights r
                JOIN r.role rr
                WHERE w IN (:workspaces)
                AND rr.name in (:roles)";

        if($user !== null){
            $dql .= " AND m.creator = :user";
        }

        $dql .= " ORDER BY m.creationDate DESC";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setParameter('roles', $roles);
        if($user !== null) {
            $query->setParameter('user', $user);
        }
        $query->setFirstResult(0)->setMaxResults($n);

        $paginator = new Paginator($query, $fetchJoinCollection = true);

        return $paginator;
    }

    public function findMessagesWithNoAuthor($executeQuery = true)
    {
        $dql = '
            SELECT m
            FROM Claroline\ForumBundle\Entity\Message m
            WHERE m.author IS NULL
        ';

        $query = $this->_em->createQuery($dql);

        return $executeQuery ? $query->getResult() : $query;
    }
}

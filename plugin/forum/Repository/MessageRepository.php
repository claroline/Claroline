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

use Claroline\ForumBundle\Entity\Forum;
use Claroline\ForumBundle\Entity\Subject;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

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

        return ($getQuery) ? $query : $query->getResult();
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

    public function findNLastByRoles(array $roles, $max = 10)
    {
        $dql = "
            SELECT m
            FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.subject s
            JOIN s.category c
            JOIN c.forum as f
            JOIN f.resourceNode n
            JOIN n.workspace w
            JOIN n.rights r
            JOIN r.role rr
            WHERE rr.name IN (:roles)
            AND (
                BIT_AND(r.mask, 1) = 1
                OR CONCAT('ROLE_WS_MANAGER_', w.guid) IN (:roles)
            )
            ORDER BY m.creationDate DESC
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setFirstResult(0)->setMaxResults($max);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function findNLastByRolesAndSubjects(array $roles, array $subjects, $max = 10)
    {
        $dql = "
            SELECT m
            FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.subject s
            JOIN s.category c
            JOIN c.forum as f
            JOIN f.resourceNode n
            JOIN n.workspace w
            JOIN n.rights r
            JOIN r.role rr
            WHERE s IN (:subjects)
            AND rr.name IN (:roles)
            AND (
                BIT_AND(r.mask, 1) = 1
                OR CONCAT('ROLE_WS_MANAGER_', w.guid) IN (:roles)
            )
            ORDER BY m.creationDate DESC
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('roles', $roles);
        $query->setParameter('subjects', $subjects);
        $query->setFirstResult(0)->setMaxResults($max);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function findNLastByWorkspacesAndRoles(array $workspaces, array $roles, $max = 10)
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
                AND rr.name IN (:roles)
                AND (
                    BIT_AND(r.mask, 1) = 1
                    OR CONCAT('ROLE_WS_MANAGER_', w.guid) IN (:roles)
                )
                ORDER BY m.creationDate DESC";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setParameter('roles', $roles);
        $query->setFirstResult(0)->setMaxResults($max);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function findNLastByWorkspacesAndRolesAndSubjects(array $workspaces, array $roles, array $subjects, $max = 10)
    {
        $dql = "
            SELECT m
            FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.subject s
            JOIN s.category c
            JOIN c.forum as f
            JOIN f.resourceNode n
            JOIN n.workspace w
            JOIN n.rights r
            JOIN r.role rr
            WHERE w IN (:workspaces)
            AND s IN (:subjects)
            AND rr.name IN (:roles)
            AND (
                BIT_AND(r.mask, 1) = 1
                OR CONCAT('ROLE_WS_MANAGER_', w.guid) IN (:roles)
            )
            ORDER BY m.creationDate DESC
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('workspaces', $workspaces);
        $query->setParameter('roles', $roles);
        $query->setParameter('subjects', $subjects);
        $query->setFirstResult(0)->setMaxResults($max);

        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function findNLastByForumAndRoles(Forum $forum, array $roles, $max = 10)
    {
        $dql = "
            SELECT m
            FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.subject s
            JOIN s.category c
            JOIN c.forum f
            JOIN f.resourceNode n
            JOIN n.workspace w
            JOIN n.rights r
            JOIN r.role rr
            WHERE f = :forum
            AND rr.name IN (:roles)
            AND (
                BIT_AND(r.mask, 1) = 1
                OR CONCAT('ROLE_WS_MANAGER_', w.guid) IN (:roles)
            )
            ORDER BY m.creationDate DESC
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('forum', $forum);
        $query->setParameter('roles', $roles);
        $query->setFirstResult(0)->setMaxResults($max);
        $paginator = new Paginator($query, true);

        return $paginator;
    }

    public function findNLastByForumAndRolesAndSubjects(Forum $forum, array $roles, array $subjects, $max = 10)
    {
        $dql = "
            SELECT m
            FROM Claroline\ForumBundle\Entity\Message m
            JOIN m.subject s
            JOIN s.category c
            JOIN c.forum f
            JOIN f.resourceNode n
            JOIN n.workspace w
            JOIN n.rights r
            JOIN r.role rr
            WHERE f = :forum
            AND s IN (:subjects)
            AND rr.name IN (:roles)
            AND (
                BIT_AND(r.mask, 1) = 1
                OR CONCAT('ROLE_WS_MANAGER_', w.guid) IN (:roles)
            )
            ORDER BY m.creationDate DESC
        ";

        $query = $this->_em->createQuery($dql);
        $query->setParameter('forum', $forum);
        $query->setParameter('roles', $roles);
        $query->setParameter('subjects', $subjects);
        $query->setFirstResult(0)->setMaxResults($max);
        $paginator = new Paginator($query, true);

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

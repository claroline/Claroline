<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Repository;

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CourseRegistrationQueue;
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityRepository;

class CourseSessionRegistrationQueueRepository extends EntityRepository
{
    public function findSessionQueuesBySession(CourseSession $session, $executeQuery = true)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            JOIN q.user u
            WHERE q.session = :session
            AND u.isRemoved = false
            ORDER BY u.lastName ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionQueuesByUser(User $user, $executeQuery = true)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            WHERE q.user = :user
            ORDER BY q.applicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findOneSessionQueueBySessionAndUser(
        CourseSession $session,
        User $user,
        $executeQuery = true
    ) {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            WHERE q.session = :session
            AND q.user = :user
            ORDER BY q.applicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findAllUnvalidatedSessionQueues()
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            WHERE q.status > 0
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAllSearchedUnvalidatedSessionQueues($search)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            JOIN q.session s
            JOIN s.course c
            WHERE q.status > 0
            AND (
                UPPER(s.name) LIKE :search
                OR UPPER(c.title) LIKE :search
                OR UPPER(c.code) LIKE :search
            )
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findUnvalidatedSessionQueuesByValidator(User $user)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            JOIN q.session s
            JOIN s.validators v
            WHERE BIT_AND(q.status, :value) = :value
            AND v = :user
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('value', CourseRegistrationQueue::WAITING_VALIDATOR);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findUnvalidatedSearchedSessionQueuesByValidator(User $user, $search)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            JOIN q.session s
            JOIN s.course c
            JOIN s.validators v
            WHERE BIT_AND(q.status, :value) = :value
            AND v = :user
            AND (
                UPPER(s.name) LIKE :search
                OR UPPER(c.title) LIKE :search
                OR UPPER(c.code) LIKE :search
            )
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('value', CourseRegistrationQueue::WAITING_VALIDATOR);
        $query->setParameter('user', $user);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findUnvalidatedSessionQueuesByOrganization(User $user)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            JOIN q.user u
            JOIN u.organizations o
            JOIN o.administrators oa
            WHERE BIT_AND(q.status, :value) = :value
            AND oa = :user
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('value', CourseRegistrationQueue::WAITING_ORGANIZATION);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findUnvalidatedSearchedSessionQueuesByOrganization(User $user, $search)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            JOIN q.session s
            JOIN s.course c
            JOIN q.user u
            JOIN u.organizations o
            JOIN o.administrators oa
            WHERE BIT_AND(q.status, :value) = :value
            AND oa = :user
            AND (
                UPPER(s.name) LIKE :search
                OR UPPER(c.title) LIKE :search
                OR UPPER(c.code) LIKE :search
            )
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('value', CourseRegistrationQueue::WAITING_ORGANIZATION);
        $query->setParameter('user', $user);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findUnvalidatedSessionQueues(User $user)
    {
        $organizations = $user->getAdministratedOrganizations()->toArray();

        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            JOIN q.session s
            JOIN s.course c
            LEFT JOIN c.organizations o
            WHERE (
                o IN (:organizations)
                OR EXISTS (
                    SELECT cu
                    FROM Claroline\CursusBundle\Entity\Cursus cu
                    JOIN cu.course cuc
                    JOIN cu.organizations cuo
                    WHERE cuc = c
                    AND cuo IN (:organizations)
                )
            )
            AND q.status = :value
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('organizations', $organizations);
        $query->setParameter('value', CourseRegistrationQueue::WAITING);

        return $query->getResult();
    }

    public function findUnvalidatedSearchedSessionQueues(User $user, $search)
    {
        $organizations = $user->getAdministratedOrganizations()->toArray();

        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            JOIN q.session s
            JOIN s.course c
            LEFT JOIN c.organizations o
            WHERE (
                o IN (:organizations)
                OR EXISTS (
                    SELECT cu
                    FROM Claroline\CursusBundle\Entity\Cursus cu
                    JOIN cu.course cuc
                    JOIN cu.organizations cuo
                    WHERE cuc = c
                    AND cuo IN (:organizations)
                )
            )
            AND q.status = :value
            AND (
                UPPER(s.name) LIKE :search
                OR UPPER(c.title) LIKE :search
                OR UPPER(c.code) LIKE :search
            )
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('organizations', $organizations);
        $query->setParameter('value', CourseRegistrationQueue::WAITING);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }
}

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
use Doctrine\ORM\EntityRepository;

class CourseRegistrationQueueRepository extends EntityRepository
{
    public function findCourseQueuesByUser(User $user, $executeQuery = true)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
            WHERE q.user = :user
            ORDER BY q.applicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findOneCourseQueueByCourseAndUser(
        Course $course,
        User $user,
        $executeQuery = true
    ) {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
            WHERE q.course = :course
            AND q.user = :user
            ORDER BY q.applicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('course', $course);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findAllUnvalidatedCourseQueues()
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
            WHERE q.status > 0
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);

        return $query->getResult();
    }

    public function findAllSearchedUnvalidatedCourseQueues($search)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
            JOIN q.course c
            WHERE q.status > 0
            AND (
                UPPER(c.title) LIKE :search
                OR UPPER(c.code) LIKE :search
            )
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }

    public function findUnvalidatedCourseQueuesByValidator(User $user)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
            JOIN q.course c
            JOIN c.validators v
            WHERE BIT_AND(q.status, :value) = :value
            AND v = :user
            ORDER BY q.applicationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('value', CourseRegistrationQueue::WAITING_VALIDATOR);
        $query->setParameter('user', $user);

        return $query->getResult();
    }

    public function findUnvalidatedSearchedCourseQueuesByValidator(User $user, $search)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
            JOIN q.course c
            JOIN c.validators v
            WHERE BIT_AND(q.status, :value) = :value
            AND v = :user
            AND (
                UPPER(c.title) LIKE :search
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

    public function findUnvalidatedCourseQueuesByOrganization(User $user)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
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

    public function findUnvalidatedSearchedCourseQueuesByOrganization(User $user, $search)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
            JOIN q.course c
            JOIN q.user u
            JOIN u.organizations o
            JOIN o.administrators oa
            WHERE BIT_AND(q.status, :value) = :value
            AND oa = :user
            AND (
                UPPER(c.title) LIKE :search
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

    public function findUnvalidatedCourseQueues(User $user)
    {
        $organizations = $user->getAdministratedOrganizations()->toArray();

        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
            JOIN q.course c
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

    public function findUnvalidatedSearchedCourseQueues(User $user, $search)
    {
        $organizations = $user->getAdministratedOrganizations()->toArray();

        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
            JOIN q.course c
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
                UPPER(c.title) LIKE :search
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

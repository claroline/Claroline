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
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\CourseSessionUser;
use Doctrine\ORM\EntityRepository;

class CourseSessionUserRepository extends EntityRepository
{
    public function findSessionUsersBySessionAndType(CourseSession $session, $userType, $executeQuery = true)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            JOIN csu.user u
            WHERE u.isRemoved = false
            AND csu.session = :session
            AND csu.userType = :userType
            ORDER BY u.lastName ASC, u.firstName ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('userType', $userType);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findOneSessionUserBySessionAndUserAndType(CourseSession $session, User $user, $userType, $executeQuery = true)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            WHERE csu.session = :session
            AND csu.user = :user
            AND csu.userType = :userType
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('user', $user);
        $query->setParameter('userType', $userType);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }

    public function findOneSessionUserBySessionAndUserAndTypes(CourseSession $session, User $user, array $userTypes)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            WHERE csu.session = :session
            AND csu.user = :user
            AND csu.userType IN (:userTypes)
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('user', $user);
        $query->setParameter('userTypes', $userTypes);

        return $query->getOneOrNullResult();
    }

    public function findSessionUsersByUser(User $user, $executeQuery = true)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            JOIN csu.session s
            JOIN s.course c
            WHERE csu.user = :user
            ORDER BY c.title ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionUsersByUserAndSearch(User $user, $search, $executeQuery = true)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            JOIN csu.session s
            JOIN s.course c
            WHERE csu.user = :user
            AND (
                UPPER(c.title) LIKE :search
                OR UPPER(s.name) LIKE :search
            )
            ORDER BY c.title ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionUsersByUserFromCoursesList(User $user, array $coursesList = [], $executeQuery = true)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            JOIN csu.session s
            JOIN s.course c
            WHERE csu.user = :user
            AND c IN (:coursesList)
            ORDER BY c.title ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('coursesList', $coursesList);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionUsersByUserAndSearchFromCoursesList(User $user, array $coursesList = [], $search = '', $executeQuery = true)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            JOIN csu.session s
            JOIN s.course c
            WHERE csu.user = :user
            AND c IN (:coursesList)
            AND (
                UPPER(c.title) LIKE :search
                OR UPPER(s.name) LIKE :search
            )
            ORDER BY c.title ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('coursesList', $coursesList);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionUsersBySession(CourseSession $session, $executeQuery = true)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            JOIN csu.user u
            WHERE csu.session = :session
            AND u.isRemoved = false
            ORDER BY u.lastName ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionUsersBySessionAndUsers(CourseSession $session, array $users, $userType, $executeQuery = true)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            WHERE csu.session = :session
            AND csu.userType = :userType
            AND csu.user IN (:users)
            ORDER BY csu.registrationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('users', $users);
        $query->setParameter('userType', $userType);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionUsersBySessionsAndUsers(array $sessions, array $users, $userType, $executeQuery = true)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            WHERE csu.userType = :userType
            AND csu.session IN (:sessions)
            AND csu.user IN (:users)
            ORDER BY csu.registrationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sessions', $sessions);
        $query->setParameter('users', $users);
        $query->setParameter('userType', $userType);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUnregisteredUsersBySession(
        CourseSession $session,
        $userType,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            WHERE u.isRemoved = false
            AND NOT EXISTS (
                SELECT csu
                FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
                WHERE csu.session = :session
                AND csu.user = u
                AND csu.userType = :userType
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('userType', $userType);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findUnregisteredUsersBySessionAndOrganizations(
        CourseSession $session,
        array $organizations,
        $userType,
        $orderedBy = 'firstName',
        $order = 'ASC',
        $executeQuery = true
    ) {
        $dql = "
            SELECT DISTINCT u
            FROM Claroline\CoreBundle\Entity\User u
            JOIN u.organizations o
            WHERE u.isRemoved = false
            AND o IN (:organizations)
            AND NOT EXISTS (
                SELECT csu
                FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
                WHERE csu.session = :session
                AND csu.user = u
                AND csu.userType = :userType
            )
            ORDER BY u.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('session', $session);
        $query->setParameter('userType', $userType);
        $query->setParameter('organizations', $organizations);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findClosedSessionUsersByUser(User $user, $userType = CourseSessionUser::LEARNER)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            JOIN csu.session s
            WHERE csu.userType = :userType
            AND csu.user = :user
            AND s.endDate < :now
            ORDER BY s.endDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);
        $query->setParameter('userType', $userType);
        $query->setParameter('now', new \DateTime());

        return $query->getResult();
    }

    public function findSessionUsersByUserAndStatusAndDate(User $user, $status, \DateTime $date, $search = '', $coursesList = null)
    {
        $dql = '
            SELECT csu
            FROM Claroline\CursusBundle\Entity\CourseSessionUser csu
            JOIN csu.session s
            JOIN s.course c
            WHERE csu.user = :user
        ';

        if (!is_null($coursesList)) {
            $dql .= '
                AND c IN (:coursesList)
            ';
        }
        switch ($status) {
            case 'open':
                $dql .= '
                    AND s.startDate <= :date
                    AND s.endDate >= :date
                ';
                break;
            case 'closed':
                $dql .= '
                    AND s.endDate < :date
                ';
                break;
            case 'unstarted':
                $dql .= '
                    AND s.startDate > :date
                ';
                break;
        }
        $dql .= '
            AND (
                UPPER(c.title) LIKE :search
                OR UPPER(s.name) LIKE :search
            )
        ';

        if ($status === 'closed') {
            $dql .= '
                ORDER BY s.endDate ASC
            ';
        } else {
            $dql .= '
                ORDER BY s.startDate ASC
            ';
        }
        $query = $this->_em->createQuery($dql);
        $query->setParameter('user', $user);

        if (!is_null($coursesList)) {
            $query->setParameter('coursesList', $coursesList);
        }
        $query->setParameter('date', $date);
        $upperSearch = strtoupper($search);
        $query->setParameter('search', "%{$upperSearch}%");

        return $query->getResult();
    }
}

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
use Claroline\CursusBundle\Entity\CourseSession;
use Doctrine\ORM\EntityRepository;

class CourseSessionRegistrationQueueRepository extends EntityRepository
{
    public function findSessionQueuesBySession(CourseSession $session, $executeQuery = true)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            WHERE q.session = :session
            ORDER BY q.applicationDate DESC
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
    )
    {
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

    public function findSessionQueuesByCourse(Course $course, $executeQuery = true)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseSessionRegistrationQueue q
            JOIN q.session s
            WHERE s.course = :course
            ORDER BY q.applicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('course', $course);

        return $executeQuery ? $query->getResult() : $query;
    }
}

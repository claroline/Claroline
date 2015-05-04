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
use Doctrine\ORM\EntityRepository;

class CourseRegistrationQueueRepository extends EntityRepository
{
    public function findCourseQueuesByCourse(Course $course, $executeQuery = true)
    {
        $dql = '
            SELECT q
            FROM Claroline\CursusBundle\Entity\CourseRegistrationQueue q
            WHERE q.course = :course
            ORDER BY q.applicationDate DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('course', $course);

        return $executeQuery ? $query->getResult() : $query;
    }

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
    )
    {
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
}

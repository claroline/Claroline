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

use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\Cursus;
use Doctrine\ORM\EntityRepository;

class CourseSessionRepository extends EntityRepository
{
    public function findSessionsByCourse(
        Course $course,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT cs
            FROM Claroline\CursusBundle\Entity\CourseSession cs
            WHERE cs.course = :course
            ORDER BY cs.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('course', $course);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionsByCourseAndStatus(
        Course $course,
        $status,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT cs
            FROM Claroline\CursusBundle\Entity\CourseSession cs
            WHERE cs.course = :course
            AND cs.sessionStatus = :status
            ORDER BY cs.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('course', $course);
        $query->setParameter('status', $status);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findDefaultSessionsByCourse(
        Course $course,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT cs
            FROM Claroline\CursusBundle\Entity\CourseSession cs
            WHERE cs.course = :course
            AND cs.defaultSession = true
            ORDER BY cs.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('course', $course);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionsByCourses(
        array $courses,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT cs
            FROM Claroline\CursusBundle\Entity\CourseSession cs
            WHERE cs.course IN (:courses)
            ORDER BY cs.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('courses', $courses);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findSessionsByCursusAndCourses(
        Cursus $cursus,
        array $courses,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT cs
            FROM Claroline\CursusBundle\Entity\CourseSession cs
            JOIN cs.cursus c
            WHERE c = :cursus
            AND cs.course IN (:courses)
            ORDER BY cs.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('cursus', $cursus);
        $query->setParameter('courses', $courses);

        return $executeQuery ? $query->getResult() : $query;
    }

    public function findDefaultPublicSessionsByCourse(
        Course $course,
        $orderedBy = 'creationDate',
        $order = 'DESC',
        $executeQuery = true
    )
    {
        $dql = "
            SELECT cs
            FROM Claroline\CursusBundle\Entity\CourseSession cs
            WHERE cs.course = (:course)
            AND cs.defaultSession = true
            AND cs.publicRegistration = true
            ORDER BY cs.{$orderedBy} {$order}
        ";
        $query = $this->_em->createQuery($dql);
        $query->setParameter('course', $course);

        return $executeQuery ? $query->getResult() : $query;
    }
}

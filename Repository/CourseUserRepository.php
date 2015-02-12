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

class CourseUserRepository extends EntityRepository
{
    public function findOneCourseUserByCourseAndUser(
        Course $course,
        User $user,
        $executeQuery = true
    )
    {
        $dql = '
            SELECT cu
            FROM Claroline\CursusBundle\Entity\CourseUser cu
            WHERE cu.course = :course
            AND cu.user = :user
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('course', $course);
        $query->setParameter('user', $user);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}

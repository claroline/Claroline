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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CursusBundle\Entity\Course;
use Doctrine\ORM\EntityRepository;

class CourseGroupRepository extends EntityRepository
{
    public function findOneCourseGroupByCourseAndGroup(
        Course $course,
        Group $group,
        $executeQuery = true
    )
    {
        $dql = '
            SELECT cg
            FROM Claroline\CursusBundle\Entity\CourseGroup cg
            WHERE cg.course = :course
            AND cg.group = :group
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('course', $course);
        $query->setParameter('group', $group);

        return $executeQuery ? $query->getOneOrNullResult() : $query;
    }
}

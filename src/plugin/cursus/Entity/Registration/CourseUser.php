<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity\Registration;

use Claroline\CursusBundle\Entity\Course;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_cursusbundle_course_course_user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="training_session_unique_user", columns={"course_id", "user_id"})
 *     }
 * )
 */
class CourseUser extends AbstractUserRegistration
{
    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Course")
     * @ORM\JoinColumn(name="course_id", nullable=false, onDelete="CASCADE")
     *
     * @var Course
     */
    private $course;

    public function getCourse(): Course
    {
        return $this->course;
    }

    public function setCourse(Course $course)
    {
        $this->course = $course;
    }
}

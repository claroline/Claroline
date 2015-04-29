<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Entity;

use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\Course;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseRegistrationQueueRepository")
 * @ORM\Table(
 *     name="claro_cursusbundle_course_registration_queue",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="course_queue_unique_course_user", columns={"course_id", "user_id"})
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"course", "user"})
 */
class CourseRegistrationQueue
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Course")
     * @ORM\JoinColumn(name="course_id", nullable=false, onDelete="CASCADE")
     */
    protected $course;

    /**
     * @ORM\Column(name="application_date", type="datetime")
     */
    protected $applicationDate;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    public function getCourse()
    {
        return $this->course;
    }

    public function setCourse(Course $course)
    {
        $this->course = $course;
    }

    public function getApplicationDate()
    {
        return $this->applicationDate;
    }

    public function setApplicationDate($applicationDate)
    {
        $this->applicationDate = $applicationDate;
    }
}

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

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseUserRepository")
 * @ORM\Table(
 *     name="claro_cursusbundle_course_user",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="cursus_user_unique_course_user",
 *             columns={"course_id", "user_id"}
 *         )
 *     }
 * )
 */
class CourseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Course",
     *     inversedBy="courseUsers"
     * )
     * @ORM\JoinColumn(name="course_id", nullable=false, onDelete="CASCADE")
     */
    protected $course;

    /**
     * @ORM\Column(name="registration_date", type="datetime", nullable=false)
     */
    protected $registrationDate;

    /**
     * @ORM\Column(name="user_type", type="integer", nullable=true)
     */
    protected $userType;

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

    public function getRegistrationDate()
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate($registrationDate)
    {
        $this->registrationDate = $registrationDate;
    }

    public function getUserType()
    {
        return $this->userType;
    }

    public function setUserType($userType)
    {
        $this->userType = $userType;
    }
}

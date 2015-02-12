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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CursusBundle\Entity\Course;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CursusBundle\Repository\CourseGroupRepository")
 * @ORM\Table(
 *     name="claro_cursusbundle_course_group",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(
 *             name="cursus_group_unique_course_group",
 *             columns={"course_id", "group_id"}
 *         )
 *     }
 * )
 */
class CourseGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Group"
     * )
     * @ORM\JoinColumn(name="group_id", nullable=false, onDelete="CASCADE")
     */
    protected $group;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Course",
     *     inversedBy="courseGroups"
     * )
     * @ORM\JoinColumn(name="course_id", nullable=false, onDelete="CASCADE")
     */
    protected $course;

    /**
     * @ORM\Column(name="registration_date", type="datetime", nullable=false)
     */
    protected $registrationDate;

    /**
     * @ORM\Column(name="group_type", type="integer", nullable=true)
     */
    protected $groupType;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(Group $group)
    {
        $this->group = $group;
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

    public function getGroupType()
    {
        return $this->groupType;
    }

    public function setGroupType($groupType)
    {
        $this->groupType = $groupType;
    }
}

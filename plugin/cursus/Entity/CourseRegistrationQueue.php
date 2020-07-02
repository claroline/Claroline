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

use Claroline\CoreBundle\Entity\Model\UuidTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="claro_cursusbundle_course_registration_queue",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="course_queue_unique_course_user", columns={"course_id", "user_id"})
 *     }
 * )
 * @DoctrineAssert\UniqueEntity({"course", "user"})
 */
class CourseRegistrationQueue extends AbstractRegistrationQueue
{
    use UuidTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CursusBundle\Entity\Course")
     * @ORM\JoinColumn(name="course_id", nullable=false, onDelete="CASCADE")
     */
    protected $course;

    public function __construct()
    {
        $this->refreshUuid();
        $this->applicationDate = new \DateTime();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCourse()
    {
        return $this->course;
    }

    public function setCourse(Course $course)
    {
        $this->course = $course;
    }
}

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

use Claroline\CursusBundle\Entity\Course;
use Claroline\CursusBundle\Entity\CursusModel;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="claro_cursus_model_course")
 * @ORM\Entity
 */
class CursusModelCourse
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\CursusModel"
     * )
     * @ORM\JoinColumn(name="cursus_model_id", onDelete="CASCADE")
     */
    protected $cursusModel;
    
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\Course"
     * )
     * @ORM\JoinColumn(name="course_id", onDelete="CASCADE")
     */
    protected $course;
    
    /**
     * @ORM\Column(name="course_order", type="integer")
     */
    protected $order;
    
    /**
     * @ORM\Column(name="course_type", type="integer")
     */
    protected $type;
    
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getCursusModel()
    {
        return $this->cursusModel;
    }

    public function setCursusModel(CursusModel $cursusModel)
    {
        $this->cursusModel = $cursusModel;
    }

    public function getCourse()
    {
        return $this->course;
    }

    public function setCourse(Course $course)
    {
        $this->course = $course;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }
}

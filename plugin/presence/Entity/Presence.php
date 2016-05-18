<?php

namespace FormaLibre\PresenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\User;

/**
  * @ORM\Entity(repositoryClass="FormaLibre\PresenceBundle\Repository\PresenceRepository")
  * @ORM\Table(name="formalibre_presencebundle_presence")
  */
 class Presence
 {
     /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\PresenceBundle\Entity\Status"
     * )
     * @ORM\JoinColumn(name="status_id", onDelete="CASCADE")
     */
    protected $status;
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_teacher_id", onDelete="CASCADE")
     */
    protected $userTeacher;
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\User"
     * )
     * @ORM\JoinColumn(name="user_student_id", onDelete="CASCADE")
     */
    protected $userStudent;
    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\PresenceBundle\Entity\Period"
     * )
     * @ORM\JoinColumn(name="period_id", onDelete="CASCADE")
     */
    protected $period;
    /**
     * @ORM\Column(name="date", type="date")
     */
    protected $date;
    /**
     * @ORM\Column(name="Comment",nullable=true)
     */
    protected $comment;
    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CursusBundle\Entity\CourseSession"
     * )
     * @ORM\JoinColumn(name="course_session", onDelete="CASCADE", nullable=true)
     */
    protected $courseSession;

     public function getId()
     {
         return $this->id;
     }

     public function setId($id)
     {
         $this->id = $id;
     }

     public function getStatus()
     {
         return $this->status;
     }

     public function setStatus($status)
     {
         $this->status = $status;
     }

     public function getUserTeacher()
     {
         return $this->userTeacher;
     }

     public function setUserTeacher(User $userTeacher)
     {
         $this->userTeacher = $userTeacher;
     }

     public function getUserStudent()
     {
         return $this->userStudent;
     }

     public function setUserStudent($userStudent)
     {
         $this->userStudent = $userStudent;
     }

     public function getPeriod()
     {
         return $this->period;
     }

     public function setPeriod(Period $period)
     {
         $this->period = $period;
     }

     public function getDate()
     {
         return $this->date;
     }

     public function setDate(\DateTime $date = null)
     {
         $this->date = $date;
     }

     public function getComment()
     {
         return $this->comment;
     }

     public function setComment($comment)
     {
         $this->comment = $comment;
     }
     public function getCourseSession()
     {
         return $this->courseSession;
     }

     public function setCourseSession($courseSession)
     {
         $this->courseSession = $courseSession;
     }
 }

<?php

namespace FormaLibre\PresenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\User;
use FormaLibre\PresenceBundle\Entity\Period;

/**
 * @ORM\Entity()
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
     * @ORM\Column(name="status")
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
        return $this->userteacher;
    }
    
    public function setUserTeacher(User $userTeacher)
    {
        $this->userTeacher = $userTeacher;
    }
    
     public function getUserStudent()
    {
        return $this->userstudent;
    }
    
    public function setUserStudent(User $userStudent)
    {
        $this->userStudent= $userStudent;
    }
    
     public function getPeriod()
    {
        return $this->userperiod;
    }
    
    public function setPeriod(User $period)
    {
        $this->Period= $period;
    }
 }
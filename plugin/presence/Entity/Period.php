<?php

namespace FormaLibre\PresenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
  * @ORM\Entity()
  * @ORM\Table(name="formalibre_presencebundle_period")
  */
 class Period
 {
     /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(
     *     targetEntity="FormaLibre\PresenceBundle\Entity\SchoolYear"
     * )
     * @ORM\JoinColumn(name="schoolYear_id", onDelete="CASCADE")
     */
    protected $schoolYearId;
    /**
     * @ORM\Column(name="num_period")
     */
    protected $numPeriod;
    /**
     * @ORM\Column(name="name",nullable=true)
     */
    protected $name;
    /**
     * @ORM\Column(name="school_date",type="date")
     */
    protected $day;
    /**
     * @ORM\Column(name="school_day")
     */
    protected $dayName;
    /**
     * @ORM\Column(name="begin_hour",type="time")
     */
    protected $beginHour;

    /**
     * @ORM\Column(name="end_hour",type="time")
     */
    protected $endHour;
    /**
     * @ORM\Column(name="visibility",type="boolean")
     */
    protected $visibility = true;

     public function getId()
     {
         return $this->id;
     }

     public function setId($id)
     {
         $this->id = $id;
     }
     public function getNumPeriod()
     {
         return $this->numPeriod;
     }

     public function setNumPeriod($numPeriod)
     {
         $this->numPeriod = $numPeriod;
     }
     public function getDay()
     {
         return $this->day;
     }

     public function setDay($day)
     {
         $this->day = $day;
     }
     public function getDayName()
     {
         return $this->dayName;
     }

     public function setDayName($dayName)
     {
         $this->dayName = $dayName;
     }
     public function getName()
     {
         return $this->name;
     }
     public function setName($name)
     {
         $this->name = $name;
     }
     public function getBeginHour()
     {
         return $this->beginHour;
     }

     public function setBeginHour($beginHour)
     {
         $this->beginHour = $beginHour;
     }
     public function getEndHour()
     {
         return $this->endHour;
     }

     public function setEndHour($endHour)
     {
         $this->endHour = $endHour;
     }
     public function getVisibility()
     {
         return $this->visibility;
     }

     public function setVisibility($visibility)
     {
         $this->visibility = $visibility;
     }
     public function getSchoolYearId()
     {
         return $this->schoolYearId;
     }

     public function setSchoolYearId($schoolYearId)
     {
         $this->schoolYearId = $schoolYearId;
     }
 }

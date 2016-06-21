<?php

namespace FormaLibre\PresenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Status.
 *
 * @ORM\Table(name="formalibre_presencebundle_schoolYear")
 * @ORM\Entity
 */
class SchoolYear
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="schoolYearName", type="string", length=255)
     */
    private $schoolYearName;
    /**
     * @ORM\Column(name="schoolYear_begin",type="date")
     */
    protected $schoolYearBegin;
    /**
     * @ORM\Column(name="schoolYear_end",type="date")
     */
    protected $schoolYearEnd;
    /**
     * @ORM\Column(name="schoolDay_begin_hour",type="time")
     */
    protected $schoolDayBeginHour;

    /**
     * @ORM\Column(name="schoolDay_end_hour",type="time")
     */
    protected $schoolDayEndHour;
    /**
     * @var string
     *
     * @ORM\Column(name="schoolYearActual", type="boolean" )
     */
    private $schoolYearActual = false;

    public function getId()
    {
        return $this->id;
    }

    public function getSchoolYearName()
    {
        return $this->schoolYearName;
    }

    public function getSchoolYearBegin()
    {
        return $this->schoolYearBegin;
    }

    public function getSchoolYearEnd()
    {
        return $this->schoolYearEnd;
    }

    public function getSchoolDayBeginHour()
    {
        return $this->schoolDayBeginHour;
    }

    public function getSchoolDayEndHour()
    {
        return $this->schoolDayEndHour;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setSchoolYearName($schoolYearName)
    {
        $this->schoolYearName = $schoolYearName;
    }

    public function setSchoolYearBegin($schoolYearBegin)
    {
        $this->schoolYearBegin = $schoolYearBegin;
    }

    public function setSchoolYearEnd($schoolYearEnd)
    {
        $this->schoolYearEnd = $schoolYearEnd;
    }

    public function setSchoolDayBeginHour($schoolDayBeginHour)
    {
        $this->schoolDayBeginHour = $schoolDayBeginHour;
    }

    public function setSchoolDayEndHour($schoolDayEndHour)
    {
        $this->schoolDayEndHour = $schoolDayEndHour;
    }
    public function getSchoolYearActual()
    {
        return $this->schoolYearActual;
    }

    public function setSchoolYearActual($schoolYearActual)
    {
        $this->schoolYearActual = $schoolYearActual;
    }
}

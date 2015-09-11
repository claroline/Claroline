<?php

namespace FormaLibre\PresenceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Status
 *
 * @ORM\Table(name="formalibre_presencebundle_schoolYear")
 * @ORM\Entity
 */
class SchoolYear
{
    /**
     * @var integer
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

    function getId() {
        return $this->id;
    }

    function getSchoolYearName() {
        return $this->schoolYearName;
    }

    function getSchoolYearBegin() {
        return $this->schoolYearBegin;
    }

    function getSchoolYearEnd() {
        return $this->schoolYearEnd;
    }

    function getSchoolDayBeginHour() {
        return $this->schoolDayBeginHour;
    }

    function getSchoolDayEndHour() {
        return $this->schoolDayEndHour;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setSchoolYearName($schoolYearName) {
        $this->schoolYearName = $schoolYearName;
    }

    function setSchoolYearBegin($schoolYearBegin) {
        $this->schoolYearBegin = $schoolYearBegin;
    }

    function setSchoolYearEnd($schoolYearEnd) {
        $this->schoolYearEnd = $schoolYearEnd;
    }

    function setSchoolDayBeginHour($schoolDayBeginHour) {
        $this->schoolDayBeginHour = $schoolDayBeginHour;
    }

    function setSchoolDayEndHour($schoolDayEndHour) {
        $this->schoolDayEndHour = $schoolDayEndHour;
    }
    function getSchoolYearActual() {
        return $this->schoolYearActual;
    }

    function setSchoolYearActual($schoolYearActual) {
        $this->schoolYearActual = $schoolYearActual;
    }



}


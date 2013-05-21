<?php

namespace Claroline\ScormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_scorm_info")
 */
class ScormInfo
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
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\ScormBundle\Entity\Scorm"
     * )
     * @ORM\JoinColumn(name="scorm_id", referencedColumnName="id")
     */
    protected $scorm;

    /**
     * @ORM\Column(name="score_raw", type="integer",nullable=true)
     */
    protected $scoreRaw;


    /**
     * @ORM\Column(name="score_min", type="integer",nullable=true)
     */
    protected $scoreMin;

    /**
     * @ORM\Column(name="score_max", type="integer",nullable=true)
     */
    protected $scoreMax;

    /**
     * @ORM\Column(name="lesson_status", type="string", length=255, nullable=true)
     */
    protected $lessonStatus;

    /**
     * @ORM\Column(name="session_time", type="integer" , nullable=true)
     */
    protected $sessionTime;

    /**
     * @ORM\Column(name="total_time", type="integer" , nullable=true)
     */
    protected $totalTime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $entry;

    /**
     * @ORM\Column(name="suspend_data", type="string", length=255, nullable=true)
     */
    protected $suspendData;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $credit;

    /**
     * @ORM\Column(name="exit_mode", type="string", length=255, nullable=true)
     */
    protected $exitMode;

    /**
     * @ORM\Column(name="lesson_location", type="string", length=255, nullable=true)
     */
    protected $lessonLocation;

    /**
     * @ORM\Column(name="lesson_mode", type="string", length=255, nullable=true)
     */
    protected $lessonMode;

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

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getScorm()
    {
        return $this->scorm;
    }

    public function setScorm($scorm)
    {
        $this->scorm = $scorm;
    }

    public function getScoreRaw()
    {
        return $this->scoreRaw;
    }

    public function setScoreRaw($scoreRaw)
    {
        $this->scoreRaw = $scoreRaw;
    }

    public function getScoreMin()
    {
        return $this->scoreMin;
    }

    public function setScoreMin($scoreMin)
    {
        $this->scoreMin = $scoreMin;
    }

    public function getScoreMax()
    {
        return $this->scoreMax;
    }

    public function setScoreMax($scoreMax)
    {
        $this->scoreMax = $scoreMax;
    }

    public function getLessonStatus()
    {
        return $this->lessonStatus;
    }

    public function setLessonStatus($lessonStatus)
    {
        $this->lessonStatus = $lessonStatus;
    }

    public function getSessionTime()
    {
        return $this->sessionTime;
    }

    public function setSessionTime($sessionTime)
    {
        $this->sessionTime = $sessionTime;
    }

    public function getTotalTime()
    {
        return $this->totalTime;
    }

    public function setTotalTime($totalTime)
    {
        $this->totalTime = $totalTime;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function setEntry($entry)
    {
        $this->entry = $entry;
    }

    public function getSuspendData()
    {
        return $this->suspendData;
    }

    public function setSuspendData($suspendData)
    {
        $this->suspendData = $suspendData;
    }

    public function getCredit()
    {
        return $this->credit;
    }

    public function setCredit($credit)
    {
        $this->credit = $credit;
    }

    public function getExitMode()
    {
        return $this->exitMode;
    }

    public function setExitMode($exitMode)
    {
        $this->exitMode = $exitMode;
    }

    public function getLessonLocation()
    {
        return $this->lessonLocation;
    }

    public function setLessonLocation($lessonLocation)
    {
        $this->lessonLocation = $lessonLocation;
    }

    public function getLessonMode()
    {
        return $this->lessonMode;
    }

    public function setLessonMode($lessonMode)
    {
        $this->lessonMode = $lessonMode;
    }
}
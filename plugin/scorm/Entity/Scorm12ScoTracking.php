<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass="Claroline\ScormBundle\Repository\Scorm12ScoTrackingRepository")
 * @ORM\Table(name="claro_scorm_12_sco_tracking")
 */
class Scorm12ScoTracking
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"api_user_min"})
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="CASCADE", nullable=false)
     * @Groups({"api_user_min"})
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\ScormBundle\Entity\Scorm12Sco")
     * @ORM\JoinColumn(name="sco_id", onDelete="CASCADE", nullable=false)
     * @Groups({"api_user_min"})
     */
    protected $sco;

    /**
     * @ORM\Column(name="score_raw", type="integer", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("scoreRaw")
     */
    protected $scoreRaw;

    /**
     * @ORM\Column(name="score_min", type="integer", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("scoreMin")
     */
    protected $scoreMin;

    /**
     * @ORM\Column(name="score_max", type="integer", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("scoreMax")
     */
    protected $scoreMax;

    /**
     * @ORM\Column(name="lesson_status", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("lessonStatus")
     */
    protected $lessonStatus;

    /**
     * @ORM\Column(name="session_time", type="integer", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("sessionTime")
     */
    protected $sessionTime;

    /**
     * @ORM\Column(name="total_time", type="integer", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("totalTime")
     */
    protected $totalTime;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("entry")
     */
    protected $entry;

    /**
     * @ORM\Column(name="suspend_data", nullable=true, length=4096)
     * @Groups({"api_user_min"})
     * @SerializedName("suspendData")
     */
    protected $suspendData;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("credit")
     */
    protected $credit;

    /**
     * @ORM\Column(name="exit_mode", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("exitMode")
     */
    protected $exitMode;

    /**
     * @ORM\Column(name="lesson_location", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("lessonLocation")
     */
    protected $lessonLocation;

    /**
     * @ORM\Column(name="lesson_mode", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("lessonMode")
     */
    protected $lessonMode;

    /**
     * @ORM\Column(name="best_score_raw", type="integer", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("bestScoreRaw")
     */
    protected $bestScoreRaw;

    /**
     * @ORM\Column(name="best_lesson_status", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("bestLessonStatus")
     */
    protected $bestLessonStatus;

    /**
     * @ORM\Column(name="is_locked", type="boolean", nullable=false)
     * @Groups({"api_user_min"})
     * @SerializedName("isLocked")
     */
    protected $isLocked;

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

    public function getSco()
    {
        return $this->sco;
    }

    public function setSco($sco)
    {
        $this->sco = $sco;
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

    public function getBestScoreRaw()
    {
        return $this->bestScoreRaw;
    }

    public function getBestLessonStatus()
    {
        return $this->bestLessonStatus;
    }

    public function getIsLocked()
    {
        return $this->isLocked;
    }

    public function setBestScoreRaw($bestScoreRaw)
    {
        $this->bestScoreRaw = $bestScoreRaw;
    }

    public function setBestLessonStatus($bestLessonStatus)
    {
        $this->bestLessonStatus = $bestLessonStatus;
    }

    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;
    }

    public function getFormattedTotalTime()
    {
        $remainingTime = $this->totalTime;
        $hours = intval($remainingTime / 360000);
        $remainingTime %= 360000;
        $minutes = intval($remainingTime / 6000);
        $remainingTime %= 6000;
        $seconds = intval($remainingTime / 100);
        $remainingTime %= 100;

        $formattedTime = '';

        if ($hours < 10) {
            $formattedTime .= '0';
        }
        $formattedTime .= $hours.':';

        if ($minutes < 10) {
            $formattedTime .= '0';
        }
        $formattedTime .= $minutes.':';

        if ($seconds < 10) {
            $formattedTime .= '0';
        }
        $formattedTime .= $seconds.'.';

        if ($remainingTime < 10) {
            $formattedTime .= '0';
        }
        $formattedTime .= $remainingTime;

        return $formattedTime;
    }
}

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

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="claro_scorm_sco_tracking")
 */
class ScoTracking
{
    use Id;
    use Uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL", nullable=true)
     *
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\ScormBundle\Entity\Sco")
     * @ORM\JoinColumn(name="sco_id", onDelete="CASCADE", nullable=false)
     *
     * @var Sco
     */
    protected $sco;

    /**
     * @ORM\Column(name="score_raw", type="integer", nullable=true)
     *
     * @var int
     */
    protected $scoreRaw;

    /**
     * @ORM\Column(name="score_min", type="integer", nullable=true)
     *
     * @var int
     */
    protected $scoreMin;

    /**
     * @ORM\Column(name="score_max", type="integer", nullable=true)
     *
     * @var int
     */
    protected $scoreMax;

    /**
     * @ORM\Column(type="float")
     *
     * @var float
     */
    protected $progression = 0;

    /**
     * For Scorm 2004 only.
     *
     * @ORM\Column(name="score_scaled", type="decimal", precision=10, scale=7, nullable=true)
     */
    protected $scoreScaled;

    /**
     * @ORM\Column(name="lesson_status", nullable=true)
     *
     * @var string
     */
    protected $lessonStatus;

    /**
     * For Scorm 2004 only.
     *
     * @ORM\Column(name="completion_status", nullable=true)
     */
    protected $completionStatus;

    /**
     * For Scorm 1.2 only.
     *
     * @ORM\Column(name="session_time", type="integer", nullable=true)
     */
    protected $sessionTime;

    /**
     * For Scorm 1.2 only.
     *
     * @ORM\Column(name="total_time_int", type="integer", nullable=true)
     */
    protected $totalTimeInt;

    /**
     * For Scorm 2004 only.
     *
     * @ORM\Column(name="total_time_string", nullable=true)
     */
    protected $totalTimeString;

    /**
     * For Scorm 1.2 only.
     *
     * @ORM\Column(nullable=true)
     */
    protected $entry;

    /**
     * For Scorm 1.2 only.
     *
     * @ORM\Column(name="suspend_data", type="text", nullable=true)
     */
    protected $suspendData;

    /**
     * For Scorm 1.2 only.
     *
     * @ORM\Column(nullable=true)
     */
    protected $credit;

    /**
     * For Scorm 1.2 only.
     *
     * @ORM\Column(name="exit_mode", nullable=true)
     */
    protected $exitMode;

    /**
     * For Scorm 1.2 only.
     *
     * @ORM\Column(name="lesson_location", nullable=true)
     */
    protected $lessonLocation;

    /**
     * For Scorm 1.2 only.
     *
     * @ORM\Column(name="lesson_mode", nullable=true)
     */
    protected $lessonMode;

    /**
     * For Scorm 1.2 only.
     *
     * @ORM\Column(name="is_locked", type="boolean", nullable=true)
     */
    protected $isLocked;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $details;

    /**
     * @ORM\Column(name="latest_date", type="datetime", nullable=true)
     */
    private $latestDate;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return Sco
     */
    public function getSco()
    {
        return $this->sco;
    }

    public function setSco(Sco $sco)
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

    public function getScoreScaled()
    {
        return $this->scoreScaled;
    }

    public function setScoreScaled($scoreScaled)
    {
        $this->scoreScaled = $scoreScaled;
    }

    public function getLessonStatus()
    {
        return $this->lessonStatus;
    }

    public function setLessonStatus($lessonStatus)
    {
        $this->lessonStatus = $lessonStatus;
    }

    public function getCompletionStatus()
    {
        return $this->completionStatus;
    }

    public function setCompletionStatus($completionStatus)
    {
        $this->completionStatus = $completionStatus;
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
        if (Scorm::SCORM_2004 === $this->sco->getScorm()->getVersion()) {
            return $this->totalTimeString;
        } else {
            return $this->totalTimeInt;
        }
    }

    public function setTotalTime($totalTime)
    {
        if (Scorm::SCORM_2004 === $this->sco->getScorm()->getVersion()) {
            $this->setTotalTimeString($totalTime);
        } else {
            $this->setTotalTimeInt($totalTime);
        }
    }

    public function getTotalTimeInt()
    {
        return $this->totalTimeInt;
    }

    public function setTotalTimeInt($totalTimeInt)
    {
        $this->totalTimeInt = $totalTimeInt;
    }

    public function getTotalTimeString()
    {
        return $this->totalTimeString;
    }

    public function setTotalTimeString($totalTimeString)
    {
        $this->totalTimeString = $totalTimeString;
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

    public function getIsLocked()
    {
        return $this->isLocked;
    }

    public function setIsLocked($isLocked)
    {
        $this->isLocked = $isLocked;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getLatestDate()
    {
        return $this->latestDate;
    }

    public function setLatestDate(\DateTime $latestDate = null)
    {
        $this->latestDate = $latestDate;
    }

    public function getProgression()
    {
        return $this->progression;
    }

    public function setProgression($progression)
    {
        $this->progression = $progression;
    }

    public function getFormattedTotalTime()
    {
        if (Scorm::SCORM_2004 === $this->sco->getScorm()->getVersion()) {
            return $this->getFormattedTotalTimeString();
        } else {
            return $this->getFormattedTotalTimeInt();
        }
    }

    public function getFormattedTotalTimeInt()
    {
        $remainingTime = $this->totalTimeInt;
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

    public function getFormattedTotalTimeString()
    {
        $pattern = '/^P([0-9]+Y)?([0-9]+M)?([0-9]+D)?T([0-9]+H)?([0-9]+M)?([0-9]+S)?$/';
        $formattedTime = '';

        if (!empty($this->totalTimeString) && 'PT' !== $this->totalTimeString && preg_match($pattern, $this->totalTimeString)) {
            $interval = new \DateInterval($this->totalTimeString);
            $time = new \DateTime();
            $time->setTimestamp(0);
            $time->add($interval);
            $timeInSecond = $time->getTimestamp();

            $hours = intval($timeInSecond / 3600);
            $timeInSecond %= 3600;
            $minutes = intval($timeInSecond / 60);
            $timeInSecond %= 60;

            if ($hours < 10) {
                $formattedTime .= '0';
            }
            $formattedTime .= $hours.':';

            if ($minutes < 10) {
                $formattedTime .= '0';
            }
            $formattedTime .= $minutes.':';

            if ($timeInSecond < 10) {
                $formattedTime .= '0';
            }
            $formattedTime .= $timeInSecond;
        } else {
            $formattedTime .= '00:00:00';
        }

        return $formattedTime;
    }
}

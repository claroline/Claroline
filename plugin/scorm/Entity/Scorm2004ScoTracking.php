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
 * @ORM\Entity(repositoryClass="Claroline\ScormBundle\Repository\Scorm2004ScoTrackingRepository")
 * @ORM\Table(name="claro_scorm_2004_sco_tracking")
 */
class Scorm2004ScoTracking
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
     * @ORM\ManyToOne(targetEntity="Claroline\ScormBundle\Entity\Scorm2004Sco")
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
     * @ORM\Column(name="score_scaled", type="decimal", precision=10, scale=7, nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("scoreScaled")
     */
    protected $scoreScaled;

    /**
     * @ORM\Column(name="completion_status", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("completionStatus")
     */
    protected $completionStatus;

    /**
     * @ORM\Column(name="success_status", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("successStatus")
     */
    protected $successStatus;

    /**
     * @ORM\Column(name="total_time", nullable=true)
     * @Groups({"api_user_min"})
     * @SerializedName("totalTime")
     */
    protected $totalTime;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     * @Groups({"api_user_min"})
     */
    protected $details;

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

    public function getScoreScaled()
    {
        return $this->scoreScaled;
    }

    public function setScoreScaled($scoreScaled)
    {
        $this->scoreScaled = $scoreScaled;
    }

    public function getTotalTime()
    {
        return $this->totalTime;
    }

    public function setTotalTime($totalTime)
    {
        $this->totalTime = $totalTime;
    }

    public function getCompletionStatus()
    {
        return $this->completionStatus;
    }

    public function setCompletionStatus($completionStatus)
    {
        $this->completionStatus = $completionStatus;
    }

    public function getSuccessStatus()
    {
        return $this->successStatus;
    }

    public function setSuccessStatus($successStatus)
    {
        $this->successStatus = $successStatus;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getFormattedTotalTime()
    {
        $pattern = '/^P([0-9]+Y)?([0-9]+M)?([0-9]+D)?T([0-9]+H)?([0-9]+M)?([0-9]+S)?$/';
        $formattedTime = '';

        if (!empty($this->totalTime) && $this->totalTime !== 'PT' && preg_match($pattern, $this->totalTime)) {
            $interval = new \DateInterval($this->totalTime);
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

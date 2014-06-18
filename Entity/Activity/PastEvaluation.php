<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Activity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\Activity\PastEvaluationRepository")
 * @ORM\Table(name="claro_activity_past_evaluation")
 */
class PastEvaluation
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
     * @ORM\JoinColumn(name="user_id", onDelete="SET NULL", nullable=true)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Activity\ActivityParameters"
     * )
     * @ORM\JoinColumn(name="activity_parameters_id", onDelete="SET NULL", nullable=true)
     */
    protected $activityParameters;

    /**
     * @ORM\Column(name="last_date", type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @ORM\Column(name="evaluation_type", nullable=true)
     */
    protected $type;

    /**
     * @ORM\Column(name="evaluation_status", nullable=true)
     */
    protected $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $duration;

    /**
     * @ORM\Column(nullable=true)
     */
    protected $score;

    /**
     * @ORM\Column(name="score_num", type="integer", nullable=true)
     */
    protected $numScore;

    /**
     * @ORM\Column(name="score_min", type="integer", nullable=true)
     */
    protected $scoreMin;

    /**
     * @ORM\Column(name="score_max", type="integer", nullable=true)
     */
    protected $scoreMax;

    /**
     * @ORM\Column(name="evaluation_comment", nullable=true)
     */
    protected $comment;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    protected $details;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Claroline\CoreBundle\Entity\Log\Log"
     * )
     * @ORM\JoinColumn(name="log_id", onDelete="SET NULL", nullable=true)
     */
    protected $log;

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

    public function getActivityParameters()
    {
        return $this->activityParameters;
    }

    public function setActivityParameters($activityParameters)
    {
        $this->activityParameters = $activityParameters;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getScore()
    {
        return $this->score;
    }

    public function setScore($score)
    {
        $this->score = $score;
    }

    public function getNumScore()
    {
        return $this->numScore;
    }

    public function setNumScore($numScore)
    {
        $this->numScore = $numScore;
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

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function setDetails($details)
    {
        $this->details = $details;
    }

    public function getLog()
    {
        return $this->log;
    }

    public function setLog($log)
    {
        $this->log = $log;
    }
}

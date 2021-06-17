<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Entity\Evaluation;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class AbstractEvaluation
{
    const STATUS_PASSED = 'passed';
    const STATUS_FAILED = 'failed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_INCOMPLETE = 'incomplete';
    const STATUS_NOT_ATTEMPTED = 'not_attempted';
    const STATUS_UNKNOWN = 'unknown';
    const STATUS_OPENED = 'opened';
    const STATUS_PARTICIPATED = 'participated';
    const STATUS_TODO = 'todo';

    const STATUS_PRIORITY = [
        self::STATUS_NOT_ATTEMPTED => 0,
        self::STATUS_TODO => 0,
        self::STATUS_UNKNOWN => 1,
        self::STATUS_OPENED => 2,
        self::STATUS_INCOMPLETE => 3,
        self::STATUS_PARTICIPATED => 4,
        self::STATUS_FAILED => 5,
        self::STATUS_COMPLETED => 6,
        self::STATUS_PASSED => 7,
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="evaluation_date", type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @ORM\Column(name="evaluation_status", nullable=true)
     */
    protected $status = self::STATUS_NOT_ATTEMPTED;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $duration = 0;

    /**
     * @ORM\Column(name="score", type="float", nullable=true)
     */
    protected $score;

    /**
     * @ORM\Column(name="score_min", type="float", nullable=true)
     */
    protected $scoreMin;

    /**
     * @ORM\Column(name="score_max", type="float", nullable=true)
     */
    protected $scoreMax;

    /**
     * @ORM\Column(name="progression", type="integer", nullable=true)
     */
    protected $progression;

    /**
     * @ORM\Column(name="progression_max", type="integer", nullable=true)
     */
    protected $progressionMax;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate(\DateTime $date = null)
    {
        $this->date = $date;
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
        return $this->duration ?? 0;
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

    public function getProgression()
    {
        return $this->progression;
    }

    public function setProgression($progression)
    {
        $this->progression = $progression;
    }

    public function getProgressionMax()
    {
        return $this->progressionMax;
    }

    public function setProgressionMax($progressionMax)
    {
        $this->progressionMax = $progressionMax;
    }

    public function isTerminated()
    {
        return self::STATUS_NOT_ATTEMPTED !== $this->status &&
            self::STATUS_INCOMPLETE !== $this->status &&
            self::STATUS_UNKNOWN !== $this->status;
    }

    public function isSuccessful()
    {
        return self::STATUS_PASSED === $this->status ||
            self::STATUS_COMPLETED === $this->status;
    }
}

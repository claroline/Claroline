<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Resource;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class AbstractResourceEvaluation
{
    const STATUS_PASSED = 'passed';
    const STATUS_FAILED = 'failed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_INCOMPLETE = 'incomplete';
    const STATUS_NOT_ATTEMPTED = 'not_attempted';
    const STATUS_UNKNOWN = 'unknown';

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
    protected $status;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $duration;

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
     * @ORM\Column(name="custom_score", nullable=true)
     */
    protected $customScore;

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

    public function getCustomScore()
    {
        return $this->customScore;
    }

    public function setCustomScore($customScore)
    {
        $this->customScore = $customScore;
    }

    public function isTerminated()
    {
        return $this->status !== self::STATUS_NOT_ATTEMPTED
            && $this->status !== self::STATUS_INCOMPLETE
            && $this->status !== self::STATUS_UNKNOWN;
    }

    public function isSuccessful()
    {
        return $this->status === self::STATUS_PASSED
            || $this->status === self::STATUS_COMPLETED;
    }
}

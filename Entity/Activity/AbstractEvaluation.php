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

use Claroline\CoreBundle\Entity\Log\Log;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
class AbstractEvaluation
{
    const TYPE_AUTOMATIC = 'automatic';
    const TYPE_MANUAL = 'manual';

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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Log\Log")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    protected $log;

    public function getId()
    {
        return $this->id;
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

    public function setLog(Log $log)
    {
        $this->log = $log;
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

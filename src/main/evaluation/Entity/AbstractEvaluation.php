<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\EvaluationBundle\Library\EvaluationInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\MappedSuperclass
 */
abstract class AbstractEvaluation implements EvaluationInterface
{
    use Id;

    const STATUS_NOT_ATTEMPTED = 'not_attempted';
    const STATUS_TODO = 'todo';
    const STATUS_UNKNOWN = 'unknown';
    const STATUS_OPENED = 'opened';
    const STATUS_PARTICIPATED = 'participated';
    const STATUS_INCOMPLETE = 'incomplete';
    const STATUS_FAILED = 'failed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PASSED = 'passed';

    const STATUS_PRIORITY = [
        self::STATUS_NOT_ATTEMPTED => 0,
        self::STATUS_TODO => 0,
        self::STATUS_UNKNOWN => 1,
        self::STATUS_OPENED => 2,
        self::STATUS_PARTICIPATED => 3,
        self::STATUS_INCOMPLETE => 4,
        self::STATUS_COMPLETED => 5,
        self::STATUS_FAILED => 6,
        self::STATUS_PASSED => 7,
    ];

    /**
     * @ORM\Column(name="evaluation_date", type="datetime", nullable=true)
     *
     * @var \DateTimeInterface
     */
    protected $date;

    /**
     * @ORM\Column(name="evaluation_status")
     *
     * @var string
     */
    protected $status = self::STATUS_NOT_ATTEMPTED;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @var int
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
     * @ORM\Column(name="progression", type="integer")
     */
    protected $progression = 0;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date = null): void
    {
        $this->date = $date;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getDuration(): int
    {
        return $this->duration ?? 0;
    }

    public function setDuration(?int $duration): void
    {
        $this->duration = $duration;
    }

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function setScore(?float $score = null): void
    {
        $this->score = $score;
    }

    public function getScoreMin(): ?float
    {
        return $this->scoreMin;
    }

    public function setScoreMin(?float $scoreMin = null): void
    {
        $this->scoreMin = $scoreMin;
    }

    public function getScoreMax(): ?float
    {
        return $this->scoreMax;
    }

    public function setScoreMax(?float $scoreMax = null): void
    {
        $this->scoreMax = $scoreMax;
    }

    public function getProgression(): float
    {
        return $this->progression;
    }

    public function setProgression(float $progression): void
    {
        $this->progression = $progression;
    }

    public function isTerminated(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_PASSED,
            self::STATUS_PARTICIPATED,
            self::STATUS_FAILED,
        ]);
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_PASSED,
        ]);
    }
}

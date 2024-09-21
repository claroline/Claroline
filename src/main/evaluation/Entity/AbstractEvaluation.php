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
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractEvaluation implements EvaluationInterface
{
    use Id;

    /** @deprecated use Claroline\EvaluationBundle\Library\EvaluationStatus instead */
    public const STATUS_NOT_ATTEMPTED = EvaluationStatus::NOT_ATTEMPTED;
    /** @deprecated use Claroline\EvaluationBundle\Library\EvaluationStatus instead */
    public const STATUS_TODO = EvaluationStatus::TODO;
    /** @deprecated use Claroline\EvaluationBundle\Library\EvaluationStatus instead */
    public const STATUS_UNKNOWN = EvaluationStatus::UNKNOWN;
    /** @deprecated use Claroline\EvaluationBundle\Library\EvaluationStatus instead */
    public const STATUS_OPENED = EvaluationStatus::OPENED;
    /** @deprecated use Claroline\EvaluationBundle\Library\EvaluationStatus instead */
    public const STATUS_PARTICIPATED = EvaluationStatus::PARTICIPATED;
    /** @deprecated use Claroline\EvaluationBundle\Library\EvaluationStatus instead */
    public const STATUS_INCOMPLETE = EvaluationStatus::INCOMPLETE;
    /** @deprecated use Claroline\EvaluationBundle\Library\EvaluationStatus instead */
    public const STATUS_FAILED = EvaluationStatus::FAILED;
    /** @deprecated use Claroline\EvaluationBundle\Library\EvaluationStatus instead */
    public const STATUS_COMPLETED = EvaluationStatus::COMPLETED;
    /** @deprecated use Claroline\EvaluationBundle\Library\EvaluationStatus instead */
    public const STATUS_PASSED = EvaluationStatus::PASSED;

    /** @deprecated use Claroline\EvaluationBundle\Library\EvaluationStatus instead */
    public const STATUS_PRIORITY = EvaluationStatus::PRIORITY;

    #[ORM\Column(name: 'evaluation_date', type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $date = null;

    #[ORM\Column(name: 'evaluation_status')]
    protected string $status = EvaluationStatus::NOT_ATTEMPTED;

    #[ORM\Column(type: 'integer', nullable: true)]
    protected int $duration = 0;

    #[ORM\Column(name: 'score', type: 'float', nullable: true)]
    protected ?float $score = null;

    #[ORM\Column(name: 'score_min', type: 'float', nullable: true)]
    protected ?float $scoreMin = 0;

    #[ORM\Column(name: 'score_max', type: 'float', nullable: true)]
    protected ?float $scoreMax = null;

    #[ORM\Column(name: 'progression', type: 'float')]
    protected ?float $progression = 0;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date = null): void
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

    public function setScore(float $score = null): void
    {
        $this->score = $score;
    }

    public function getRelativeScore(): ?float
    {
        if (!empty($this->scoreMax)) {
            return $this->score ? $this->score / $this->scoreMax : null;
        }

        return null;
    }

    public function getScoreMin(): ?float
    {
        return $this->scoreMin;
    }

    public function setScoreMin(float $scoreMin = null): void
    {
        $this->scoreMin = $scoreMin;
    }

    public function getScoreMax(): ?float
    {
        return $this->scoreMax;
    }

    public function setScoreMax(float $scoreMax = null): void
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
        return EvaluationStatus::isTerminated($this->status);
    }
}

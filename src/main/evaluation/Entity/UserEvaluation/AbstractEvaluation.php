<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\EvaluationBundle\Entity\UserEvaluation;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Claroline\AppBundle\Entity\Meta\ArchivedAt;
use Claroline\EvaluationBundle\Library\EvaluationInterface;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractEvaluation implements EvaluationInterface
{
    use Id;
    use Uuid;
    use ArchivedAt;

    #[ORM\Column(name: 'started_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $startedAt = null;

    #[ORM\Column(name: 'ended_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $endedAt = null;

    #[ORM\Column(name: 'evaluation_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTimeInterface $lastActivityAt = null;

    #[ORM\Column(name: 'evaluation_status')]
    protected string $status = EvaluationStatus::NOT_ATTEMPTED;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected int $duration = 0;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    protected ?float $score = null;

    #[ORM\Column(name: 'score_min', type: Types::FLOAT, nullable: true)]
    protected ?float $scoreMin = 0;

    #[ORM\Column(name: 'score_max', type: Types::FLOAT, nullable: true)]
    protected ?float $scoreMax = null;

    #[ORM\Column(type: Types::FLOAT)]
    protected ?float $progression = 0;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt = null): void
    {
        $this->startedAt = $startedAt;
    }

    public function getEndedAt(): ?\DateTimeInterface
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeInterface $endedAt = null): void
    {
        $this->endedAt = $endedAt;
    }

    public function getLastActivityAt(): ?\DateTimeInterface
    {
        return $this->lastActivityAt;
    }

    public function setLastActivityAt(?\DateTimeInterface $lastActivityAt = null): void
    {
        $this->lastActivityAt = $lastActivityAt;
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
        return EvaluationStatus::isTerminated($this->status);
    }
}

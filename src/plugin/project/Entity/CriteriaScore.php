<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ProjectBundle\Entity;

use Claroline\AppBundle\Entity\Identifier\Id;
use Claroline\AppBundle\Entity\Identifier\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * An individual score for a single criterion within a rubric-based correction.
 * Each CriteriaScore links a Correction to an EvaluationCriteria
 * with the score and optional comment given by the trainer.
 */
#[ORM\Table(name: 'claro_project_criteria_score')]
#[ORM\Entity]
class CriteriaScore
{
    use Id;
    use Uuid;

    /**
     * Score given for this criterion (between 0 and the criterion's scoreMax).
     */
    #[ORM\Column(type: Types::FLOAT)]
    private float $score = 0;

    /**
     * Optional comment from the trainer for this specific criterion.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    /**
     * The correction this score belongs to.
     */
    #[ORM\JoinColumn(name: 'correction_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Correction::class, inversedBy: 'criteriaScores')]
    private ?Correction $correction = null;

    /**
     * The evaluation criterion being scored.
     */
    #[ORM\JoinColumn(name: 'criteria_id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: EvaluationCriteria::class)]
    private ?EvaluationCriteria $criteria = null;

    public function __construct()
    {
        $this->refreshUuid();
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function setScore(float $score): void
    {
        $this->score = $score;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getCorrection(): ?Correction
    {
        return $this->correction;
    }

    public function setCorrection(?Correction $correction): void
    {
        $this->correction = $correction;
    }

    public function getCriteria(): ?EvaluationCriteria
    {
        return $this->criteria;
    }

    public function setCriteria(?EvaluationCriteria $criteria): void
    {
        $this->criteria = $criteria;
    }
}

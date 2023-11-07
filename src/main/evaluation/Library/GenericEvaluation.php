<?php

namespace Claroline\EvaluationBundle\Library;

final class GenericEvaluation implements EvaluationInterface
{
    private ?float $progression = 0;
    private ?float $score = null;
    private ?float $scoreMax = null;

    public function __construct(?float $progression = 0, float $scoreMax = null, float $score = null)
    {
        if ($progression < 0 || $progression > 100) {
            throw new \InvalidArgumentException('progression should be a percentage (range: 0-100).');
        }

        $this->progression = $progression;
        $this->scoreMax = $scoreMax;
        $this->score = $score;
    }

    /**
     * Get the completion percentage the evaluation.
     */
    public function getProgression(): float
    {
        return $this->progression;
    }

    /**
     * Get the user score for the evaluation.
     */
    public function getScore(): ?float
    {
        return $this->score;
    }

    /**
     * Get the maximum score of the evaluation.
     */
    public function getScoreMax(): ?float
    {
        return $this->scoreMax;
    }

    /**
     * Check if the evaluation is ended.
     */
    public function isTerminated(): bool
    {
        return 100 <= $this->progression;
    }

    public function getStatus(): ?string
    {
        return EvaluationStatus::UNKNOWN;
    }
}

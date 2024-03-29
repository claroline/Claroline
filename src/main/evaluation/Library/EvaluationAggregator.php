<?php

namespace Claroline\EvaluationBundle\Library;

class EvaluationAggregator implements EvaluationInterface
{
    /**
     * The list of evaluation which participate in the progression of the aggregate.
     *
     * @var EvaluationInterface[]
     */
    private array $progressionEvaluations = [];

    /**
     * The list of evaluation which participate in the score of the aggregate.
     * NB. if an evaluation is used in the aggregate score, it's also used in its progression.
     *
     * @var EvaluationInterface[]
     */
    private array $scoreEvaluations = [];

    private EvaluationStatusChecker $statusChecker;

    public function __construct(array $statusCheckers = [])
    {
        $this->statusChecker = new EvaluationStatusChecker($statusCheckers);
    }

    public function addEvaluation(EvaluationInterface $evaluation, bool $useScore = false): void
    {
        $this->progressionEvaluations[] = $evaluation;
        if ($useScore) {
            $this->scoreEvaluations[] = $evaluation;
        }
    }

    /**
     * @return EvaluationInterface[]
     */
    public function getEvaluations(): array
    {
        return $this->progressionEvaluations;
    }

    /**
     * The progression of an aggregator is the sum of the progression of all the required evaluation.
     */
    public function getProgression(): float
    {
        if (empty($this->progressionEvaluations)) {
            // no required evaluation
            return 0;
        }

        $totalProgression = array_reduce($this->progressionEvaluations, function (float $progression, EvaluationInterface $evaluation) {
            return $progression + ($evaluation->isTerminated() ? 100 : $evaluation->getProgression());
        }, 0);

        return $totalProgression / count($this->progressionEvaluations);
    }

    /**
     * Get the sum of all the evaluations score in the aggregate.
     * NB. Score is only available once all the evaluations of the aggregate are considered terminated.
     */
    public function getScore(): ?float
    {
        if (!$this->isTerminated()) {
            // score is only available when the evaluation is terminated
            return null;
        }

        return array_reduce($this->scoreEvaluations, function (float $score, EvaluationInterface $evaluation) {
            return $evaluation->getScore() ? $score + $evaluation->getScore() : $score;
        }, 0);
    }

    /**
     * Get the sum of all the evaluations score max in the aggregate.
     * NB. Score is only available once all the evaluations of the aggregate are considered terminated.
     */
    public function getScoreMax(): ?float
    {
        if (!$this->isTerminated()) {
            // score is only available when the evaluation is terminated
            return null;
        }

        return array_reduce($this->scoreEvaluations, function (float $scoreMax, EvaluationInterface $evaluation) {
            return $evaluation->getScoreMax() ? $scoreMax + $evaluation->getScoreMax() : $scoreMax;
        }, 0);
    }

    /**
     * An aggregate is considered terminated if all the evaluations used for its progression are terminated.
     */
    public function isTerminated(): bool
    {
        foreach ($this->progressionEvaluations as $evaluation) {
            if (!$evaluation->isTerminated()) {
                // there is one non terminated evaluation, the aggregate is not terminated
                return false;
            }
        }

        return true;
    }

    public function getStatus(): ?string
    {
        return $this->statusChecker->getStatus($this);
    }
}

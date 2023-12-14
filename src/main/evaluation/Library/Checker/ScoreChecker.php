<?php

namespace Claroline\EvaluationBundle\Library\Checker;

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationInterface;

class ScoreChecker implements CheckerInterface
{
    /**
     * The percentage (0-100) of the score to obtain.
     */
    private float $successScore;

    public function __construct(float $successScore)
    {
        if ($successScore < 0 || $successScore > 100) {
            throw new \InvalidArgumentException('successScore should be a percentage (range: 0-100).');
        }

        $this->successScore = $successScore;
    }

    public function supports(EvaluationInterface $evaluation): bool
    {
        return !empty($evaluation->getScoreMax());
    }

    public function vote(EvaluationInterface $evaluation): ?string
    {
        if (empty($this->successScore)) {
            // no success threshold is defined, nothing to do here
            return null;
        }

        if (!$evaluation->isTerminated()) {
            // score is only available when the evaluation is terminated
            return null;
        }

        $successScore = ($this->successScore * $evaluation->getScoreMax()) / 100;
        if ($evaluation->getScore() >= $successScore) {
            // condition is met
            return AbstractEvaluation::STATUS_PASSED;
        }

        // condition is not met
        return AbstractEvaluation::STATUS_FAILED;
    }
}

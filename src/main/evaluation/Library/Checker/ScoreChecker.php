<?php

namespace Claroline\EvaluationBundle\Library\Checker;

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationInterface;

class ScoreChecker implements CheckerInterface
{
    /** @var float */
    private $successScore;

    public function __construct(?float $successScore)
    {
        $this->successScore = $successScore;
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

        if (empty($evaluation->getScoreMax())) {
            // can't vote if the evaluation as no score
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

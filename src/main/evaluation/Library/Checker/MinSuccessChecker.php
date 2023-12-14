<?php

namespace Claroline\EvaluationBundle\Library\Checker;

use Claroline\EvaluationBundle\Library\EvaluationAggregator;
use Claroline\EvaluationBundle\Library\EvaluationInterface;
use Claroline\EvaluationBundle\Library\EvaluationStatus;

class MinSuccessChecker implements CheckerInterface
{
    private ?float $minSuccess = null;

    public function __construct(?float $minSuccess)
    {
        $this->minSuccess = $minSuccess;
    }

    public function supports(EvaluationInterface $evaluation): bool
    {
        return $evaluation instanceof EvaluationAggregator;
    }

    /**
     * @param EvaluationAggregator $evaluation
     */
    public function vote(EvaluationInterface $evaluation): ?string
    {
        if (empty($this->minSuccess)) {
            return null;
        }

        if (!$evaluation->isTerminated()) {
            // only checks if the evaluation is terminated
            return null;
        }

        // if there are not enough evaluations in the aggregator,
        // set the threshold to the nb of evaluations
        $minSuccess = $this->minSuccess > count($evaluation->getEvaluations()) ? count($evaluation->getEvaluations()) : $this->minSuccess;

        $success = 0;
        foreach ($evaluation->getEvaluations() as $childEvaluation) {
            if (EvaluationStatus::PASSED === $childEvaluation->getStatus()) {
                ++$success;
            }

            if ($success >= $minSuccess) {
                // condition is met
                return EvaluationStatus::PASSED;
            }
        }

        // condition is not met
        return EvaluationStatus::FAILED;
    }
}

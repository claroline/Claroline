<?php

namespace Claroline\EvaluationBundle\Library\Checker;

use Claroline\EvaluationBundle\Library\EvaluationAggregator;
use Claroline\EvaluationBundle\Library\EvaluationInterface;
use Claroline\EvaluationBundle\Library\EvaluationStatus;

class MaxFailedChecker implements CheckerInterface
{
    private ?float $maxFailed = null;

    public function __construct(?float $maxFailed)
    {
        $this->maxFailed = $maxFailed;
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
        if (empty($this->maxFailed)) {
            // no failed threshold is defined, nothing to do here
            return null;
        }

        if (!$evaluation->isTerminated()) {
            // only checks if the evaluation is terminated
            return null;
        }

        // if there are not enough evaluations in the aggregator,
        // set the threshold to the nb of evaluations
        $maxFailed = $this->maxFailed > count($evaluation->getEvaluations()) ? count($evaluation->getEvaluations()) : $this->maxFailed;

        $failed = 0;
        foreach ($evaluation->getEvaluations() as $childEvaluation) {
            if (EvaluationStatus::FAILED === $childEvaluation->getStatus()) {
                ++$failed;
            }

            if ($failed > $maxFailed) {
                // condition is not met
                return EvaluationStatus::FAILED;
            }
        }

        // condition is met
        return EvaluationStatus::PASSED;
    }
}

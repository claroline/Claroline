<?php

namespace Claroline\EvaluationBundle\Library\Checker;

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationInterface;

class ProgressionChecker implements CheckerInterface
{
    /** @var float */
    private $threshold;

    public function __construct(?float $threshold = 100)
    {
        $this->threshold = $threshold;
    }

    public function vote(EvaluationInterface $evaluation): ?string
    {
        if (0 === $evaluation->getProgression()) {
            return AbstractEvaluation::STATUS_NOT_ATTEMPTED;
        }

        if ($this->threshold > $evaluation->getProgression()) {
            return AbstractEvaluation::STATUS_INCOMPLETE;
        }

        return AbstractEvaluation::STATUS_COMPLETED;
    }
}

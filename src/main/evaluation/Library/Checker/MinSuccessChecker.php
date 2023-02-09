<?php

namespace Claroline\EvaluationBundle\Library\Checker;

use Claroline\EvaluationBundle\Library\EvaluationInterface;

class MinSuccessChecker implements CheckerInterface
{
    /** @var int */
    private $minSuccess;

    public function __construct(?float $minSuccess)
    {
        $this->minSuccess = $minSuccess;
    }

    public function vote(EvaluationInterface $evaluation): ?string
    {
        if (empty($this->minSuccess)) {
            return null;
        }

        return null;
    }
}

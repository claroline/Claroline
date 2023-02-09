<?php

namespace Claroline\EvaluationBundle\Library\Checker;

use Claroline\EvaluationBundle\Library\EvaluationInterface;

class MaxFailedChecker implements CheckerInterface
{
    /** @var int */
    private $maxFailed;

    public function __construct(?float $maxFailed)
    {
        $this->maxFailed = $maxFailed;
    }

    public function vote(EvaluationInterface $evaluation): ?string
    {
        if (empty($this->maxFailed)) {
            return null;
        }

        return null;
    }
}

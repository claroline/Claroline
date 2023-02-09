<?php

namespace Claroline\EvaluationBundle\Library\Checker;

use Claroline\EvaluationBundle\Library\EvaluationInterface;

interface CheckerInterface
{
    public function vote(EvaluationInterface $evaluation): ?string;
}

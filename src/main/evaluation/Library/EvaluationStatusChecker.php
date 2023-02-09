<?php

namespace Claroline\EvaluationBundle\Library;

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Library\Checker\CheckerInterface;

class EvaluationStatusChecker
{
    /** @var CheckerInterface[] */
    private $checkers;

    public function __construct(array $checkers)
    {
        $this->checkers = $checkers;
    }

    public function getStatus(EvaluationInterface $evaluation): ?string
    {
        $status = null;

        foreach ($this->checkers as $checker) {
            $checkerStatus = $checker->vote($evaluation);
            if ($checkerStatus && (!$status || AbstractEvaluation::STATUS_PRIORITY[$checkerStatus] > AbstractEvaluation::STATUS_PRIORITY[$status])) {
                $status = $checkerStatus;
            }
        }

        return $status;
    }
}

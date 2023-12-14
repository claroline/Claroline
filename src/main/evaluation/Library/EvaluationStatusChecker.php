<?php

namespace Claroline\EvaluationBundle\Library;

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Library\Checker\CheckerInterface;

class EvaluationStatusChecker
{
    /** @var CheckerInterface[] */
    private array $checkers;

    public function __construct(array $checkers)
    {
        $this->checkers = $checkers;
    }

    /**
     * Gets the status of an evaluation based on the defined checkers.
     * It will return the highest status returned by checkers or
     * if one checker fails, the whole evaluation will be considered failed too.
     */
    public function getStatus(EvaluationInterface $evaluation): ?string
    {
        $status = null;

        foreach ($this->checkers as $checker) {
            if ($checker->supports($evaluation)) {
                $checkerStatus = $checker->vote($evaluation);
                if ($checkerStatus && (!$status || AbstractEvaluation::STATUS_PRIORITY[$checkerStatus] > AbstractEvaluation::STATUS_PRIORITY[$status])) {
                    $status = $checkerStatus;
                }
            }
        }

        return $status;
    }
}

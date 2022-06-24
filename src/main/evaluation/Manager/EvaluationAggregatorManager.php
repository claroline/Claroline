<?php

namespace Claroline\EvaluationBUndle\Manager;

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;

class EvaluationAggregatorManager
{
    /**
     * @param AbstractEvaluation[] $evaluations
     */
    public function computeProgression(array $evaluations): float
    {
        $progression = 0;
        $progressionMax = 0;

        foreach ($evaluations as $evaluation) {
            $progression += $evaluation->getProgression() ?? 0;
            $progressionMax += $evaluation->getProgressionMax() ?? 0;
        }

        if ($progressionMax) {
            return ($progression / $progressionMax) * 100;
        }

        return 0;
    }

    /**
     * @param AbstractEvaluation[] $evaluations
     */
    public function computeStatus(array $evaluations): string
    {
        $status = AbstractEvaluation::STATUS_COMPLETED;

        foreach ($evaluations as $evaluation) {
            if (!$evaluation->isTerminated()) {
                $status = AbstractEvaluation::STATUS_INCOMPLETE;
            }
            /*switch ($evaluation->getStatus()) {
                case AbstractEvaluation::STATUS_FAILED:
                    $status = AbstractEvaluation::STATUS_FAILED;
                    break 2; // no need to go further
                case AbstractEvaluation::STATUS_NOT_ATTEMPTED:
                case AbstractEvaluation::STATUS_TODO:
                case AbstractEvaluation::STATUS_INCOMPLETE:
                    $status = AbstractEvaluation::STATUS_INCOMPLETE;
                    break 2; // no need to go further
            }*/
        }

        return $status;
    }

    /**
     * @param AbstractEvaluation[] $evaluations
     */
    public function computeScore(array $evaluations): array
    {
        $score = 0;
        $scoreMax = 0;

        // TODO : weighting coefficient. So I need to have access to the EvaluatedInterface
        foreach ($evaluations as $evaluation) {
            $score += $evaluation->getScore() ?? 0;
            $scoreMax += $evaluation->getScoreMax() ?? 0;
        }

        return [
            'score' => $score,
            'scoreMax' => $scoreMax,
        ];
    }
}

<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;

abstract class AbstractEvaluationManager
{
    protected function updateEvaluation(AbstractEvaluation $evaluation, ?array $data = [], ?\DateTime $date = null): AbstractEvaluation
    {
        $evaluationDate = $date ?? new \DateTime();
        if (empty($evaluation->getDate()) || $evaluationDate > $evaluation->getDate()) {
            $evaluation->setDate($evaluationDate);
        }

        if (isset($data['duration'])) {
            $evaluation->setDuration($evaluation->getDuration() + $data['duration']);
        }

        if (isset($data['status'])) {
            $this->updateEvaluationStatus($evaluation, $data['status']);
        }

        if (!empty($data['scoreMax'])) {
            $this->updateEvaluationScore($evaluation, $data['scoreMax'], $data['score'] ?? null, $data['scoreMin'] ?? null);
        }

        if (isset($data['progression'])) {
            $evaluation->setProgression($data['progression']);
            $evaluation->setProgressionMax($data['progressionMax'] ?? 100); // TODO : for retro-compatibility
        }

        return $evaluation;
    }

    private function updateEvaluationStatus(AbstractEvaluation $evaluation, string $status): ?AbstractEvaluation
    {
        if (AbstractEvaluation::STATUS_PRIORITY[$status] > AbstractEvaluation::STATUS_PRIORITY[$evaluation->getStatus()]) {
            $evaluation->setStatus($status);
        }

        return $evaluation;
    }

    private function updateEvaluationScore(AbstractEvaluation $evaluation, float $scoreMax, ?float $score = null, ?float $scoreMin = null): AbstractEvaluation
    {
        $oldScore = $evaluation->getScore() ? $evaluation->getScore() / $evaluation->getScoreMax() : null;
        $newScore = $score ? $score / $scoreMax : null;

        // update evaluation score if the user has never been evaluated, has a better score
        if (is_null($oldScore) || $newScore >= $oldScore) {
            $evaluation->setScore($score);
            $evaluation->setScoreMax($scoreMax);
            $evaluation->setScoreMin($scoreMin);
        }

        return $evaluation;
    }

    protected function computeStatus(AbstractEvaluation $evaluation): string
    {
        $newStatus = $evaluation->getStatus() ?? AbstractEvaluation::STATUS_NOT_ATTEMPTED;

        // checks progression
        if (0 !== $evaluation->getProgression() && 100 > $evaluation->getProgression()) {
            $newStatus = AbstractEvaluation::STATUS_INCOMPLETE;
        } elseif (100 <= $evaluation->getProgression()) {
            $newStatus = AbstractEvaluation::STATUS_COMPLETED;
        }

        // checks score if any

        return $newStatus;
    }
}

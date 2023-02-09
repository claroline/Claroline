<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;

abstract class AbstractEvaluationManager
{
    /**
     * Updates some evaluation data and return whether the progression of the evaluation has changed.
     * (aka. the score, status or progression has been updated).
     */
    protected function updateEvaluation(AbstractEvaluation $evaluation, ?array $data = [], ?\DateTimeInterface $date = null): array
    {
        $changes = [
            'status' => false,
            'progression' => false,
            'score' => false,
        ];

        $evaluationDate = $date ?? new \DateTime();
        if (empty($evaluation->getDate()) || $evaluationDate > $evaluation->getDate()) {
            $evaluation->setDate($evaluationDate);
        }

        if (isset($data['duration'])) {
            $evaluation->setDuration($evaluation->getDuration() + $data['duration']);
        }

        if (isset($data['status'])) {
            $previousStatus = $evaluation->getStatus();
            $this->updateEvaluationStatus($evaluation, $data['status']);

            if ($previousStatus !== $evaluation->getStatus()) {
                $changes['status'] = true;
            }
        }

        if (!empty($data['scoreMax'])) {
            $score = $data['score'] ?? null;
            $scoreMin = $data['scoreMin'] ?? null;

            if ($score !== $evaluation->getScore() || $scoreMin !== $evaluation->getScoreMin() || $data['scoreMax'] !== $evaluation->getScoreMax()) {
                $changes['score'] = true;
            }

            $this->updateEvaluationScore($evaluation, $data['scoreMax'], $score, $scoreMin);
        }

        if (isset($data['progression']) && $data['progression'] > $evaluation->getProgression()) {
            // only update the evaluation if the user progression has increased
            $evaluation->setProgression($data['progression']);
            $changes['progression'] = true;
        }

        return $changes;
    }

    private function updateEvaluationStatus(AbstractEvaluation $evaluation, string $status): AbstractEvaluation
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
}

<?php

namespace Claroline\EvaluationBundle\Manager;

use Claroline\EvaluationBundle\Entity\AbstractEvaluation;

abstract class AbstractEvaluationManager
{
    /**
     * Updates some evaluation data and return whether the progression of the evaluation has changed.
     * (aka. the score, status or progression has been updated).
     */
    protected function updateEvaluation(AbstractEvaluation $evaluation, ?array $data = [], \DateTimeInterface $date = null): array
    {
        $changes = [
            'status' => false,
            'progression' => false,
            'score' => false,
        ];

        if (isset($data['duration'])) {
            $evaluation->setDuration(round($evaluation->getDuration() + $data['duration']));
        }

        if (isset($data['status'])) {
            $previousStatus = $evaluation->getStatus();
            $this->updateEvaluationStatus($evaluation, $data['status']);

            if ($previousStatus !== $evaluation->getStatus()) {
                $changes['status'] = true;
            }
        }

        if (!empty($data['scoreMax'])) {
            $oldScore = $evaluation->getRelativeScore();
            $this->updateEvaluationScore($evaluation, $data['scoreMax'], $data['score'] ?? null, $data['scoreMin'] ?? null);

            // checks if the user score has changed
            // ATTENTION : never directly compare floats together !
            // @see https://www.php.net/manual/en/language.types.float.php
            // In this case, checking for changes higher than 0.001 is safe because we round
            // the result for users to 2 digits anyway
            if (abs($oldScore - $evaluation->getRelativeScore()) > 0.001) {
                $changes['score'] = true;
            }
        }

        if (isset($data['progression']) && $data['progression'] > $evaluation->getProgression()) {
            // only update the evaluation if the user progression has increased
            $evaluation->setProgression($data['progression']);
            $changes['progression'] = true;
        }

        if (empty($evaluation->getDate()) || $changes['score'] || $changes['progression'] || $changes['status']) {
            $evaluationDate = $date ?? new \DateTime();
            // only updates evaluation date if something interesting changes
            if (empty($evaluation->getDate()) || $evaluationDate > $evaluation->getDate()) {
                $evaluation->setDate($evaluationDate);
            }
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

    private function updateEvaluationScore(AbstractEvaluation $evaluation, float $scoreMax, float $score = null, float $scoreMin = null): AbstractEvaluation
    {
        $oldScore = $evaluation->getRelativeScore();
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

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
            $this->updateEvaluationProgression($evaluation, $data['progression'], $data['progressionMax'] ?? 100);
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

        // update evaluation score if the user has never been evaluated, has a better score or if the max score has changed
        if (is_null($oldScore) || $newScore >= $oldScore) {
            $evaluation->setScore($score);
            $evaluation->setScoreMax($scoreMax);
            $evaluation->setScoreMin($scoreMin);
        }

        return $evaluation;
    }

    private function updateEvaluationProgression(AbstractEvaluation $evaluation, float $progression, float $progressionMax): AbstractEvaluation
    {
        $newProgression = $progression / $progressionMax;

        $oldProgression = !empty($evaluation->getProgression()) ? $evaluation->getProgression() : 0;
        $oldProgressionMax = !empty($evaluation->getProgressionMax()) ? $evaluation->getProgressionMax() : 100;
        $oldProgression = $oldProgression / $oldProgressionMax;

        if ($newProgression >= $oldProgression) {
            $evaluation->setProgression($progression);
            $evaluation->setProgressionMax($progressionMax);
        }

        return $evaluation;
    }
}

<?php

namespace Claroline\ProjectBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\ProjectBundle\Entity\Correction;

class EvaluationManager
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly ResourceEvaluationManager $resourceEvalManager
    ) {
    }

    /**
     * Updates the Claroline evaluation system when a correction is submitted.
     * Creates a ResourceAttempt with the score and status (PASSED/FAILED).
     */
    public function updateUserEvaluation(Correction $correction): void
    {
        $project = $correction->getProject();
        $user = $correction->getUser();

        if (!$project || !$user) {
            return;
        }

        $resourceNode = $project->getResourceNode();
        if (!$resourceNode) {
            return;
        }

        $score = $correction->getScore();
        $scoreMax = $project->getScoreMax();

        // Determine status based on score
        $status = EvaluationStatus::COMPLETED;
        if (null !== $score && $scoreMax > 0) {
            // Use the resource evaluation's scoreToPass if configured, otherwise default to 50%
            $userEval = $this->resourceEvalManager->getUserEvaluation($resourceNode, $user, false);
            $scoreToPass = 50; // default percentage

            $relativeScore = ($score / $scoreMax) * 100;
            $status = $relativeScore >= $scoreToPass ? EvaluationStatus::PASSED : EvaluationStatus::FAILED;
        }

        $this->resourceEvalManager->createAttempt(
            $resourceNode,
            $user,
            [
                'status' => $status,
                'score' => $score,
                'scoreMax' => $scoreMax,
                'progression' => 100,
            ]
        );
    }
}

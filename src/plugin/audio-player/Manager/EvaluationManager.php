<?php

namespace Claroline\AudioPlayerBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Repository\UserEvaluation\ResourceAttemptRepository;

class EvaluationManager
{
    private ResourceEvaluationManager $resourceEvalManager;
    private ResourceAttemptRepository $resourceEvalRepo;

    public function __construct(
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->resourceEvalManager = $resourceEvalManager;
        $this->resourceEvalRepo = $om->getRepository(ResourceAttempt::class);
    }

    /**
     * Fetch or create resource user evaluation.
     */
    public function getResourceUserEvaluation(ResourceNode $node, User $user): ResourceEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($node, $user);
    }

    public function update(ResourceNode $node, User $user, float $currentTime = 0, float $totalTime = 0): ResourceAttempt
    {
        $evaluation = $this->resourceEvalRepo->findOneInProgress($node, $user);

        $progression = $currentTime;
        $progressionMax = $totalTime;

        $status = EvaluationStatus::NOT_ATTEMPTED;
        if ($progressionMax) {
            $progression = ($progression / $progressionMax) * 100;

            // mark the video as finished if the user has watched over 90% of it
            if ($progression >= 90) {
                $progression = 100;
            }

            if ($progression >= 100) {
                $status = EvaluationStatus::COMPLETED;
            } else {
                $status = EvaluationStatus::INCOMPLETE;
            }
        }

        $evaluationData = [
            'status' => $status,
            'progression' => $progression,
        ];

        if ($evaluation) {
            return $this->resourceEvalManager->updateAttempt($evaluation, $evaluationData);
        }

        return $this->resourceEvalManager->createAttempt(
            $node,
            $user,
            $evaluationData
        );
    }
}

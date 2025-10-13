<?php

namespace Claroline\BigBlueButtonBundle\Manager;

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
    private ResourceAttemptRepository $attemptRepository;

    public function __construct(
        ObjectManager $om,
        private readonly ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->attemptRepository = $om->getRepository(ResourceAttempt::class);
    }

    public function getResourceUserEvaluation(ResourceNode $node, User $user): ?ResourceEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($node, $user);
    }

    /**
     * Marks the BBB evaluation as participated.
     * Called when a user opens a BBB.
     */
    public function update(ResourceNode $resourceNode, User $user): ResourceAttempt
    {
        $evaluation = $this->attemptRepository->findOneInProgress($resourceNode, $user);

        $evaluationData = [
            'status' => EvaluationStatus::COMPLETED,
            'progression' => 100,
        ];

        if ($evaluation) {
            return $this->resourceEvalManager->updateAttempt($evaluation);
        }

        return $this->resourceEvalManager->createAttempt(
            $resourceNode,
            $user,
            $evaluationData
        );
    }
}

<?php

namespace Claroline\BigBlueButtonBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Repository\Resource\ResourceEvaluationRepository;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;

class EvaluationManager
{
    private ResourceEvaluationManager $resourceEvalManager;
    private ResourceEvaluationRepository $attemptRepository;

    public function __construct(
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->resourceEvalManager = $resourceEvalManager;
        $this->attemptRepository = $om->getRepository(ResourceEvaluation::class);
    }

    /**
     * Marks the BBB evaluation as participated.
     * Called when a user opens a BBB.
     */
    public function update(ResourceNode $resourceNode, User $user): ResourceEvaluation
    {
        $evaluation = $this->attemptRepository->findOneInProgress($resourceNode, $user);

        $evaluationData = [
            'status' => AbstractEvaluation::STATUS_PARTICIPATED,
            'progression' => 100,
        ];

        if ($evaluation) {
            return $this->resourceEvalManager->updateAttempt($evaluation, );
        }

        return $this->resourceEvalManager->createAttempt(
            $resourceNode,
            $user,
            $evaluationData
        );
    }
}

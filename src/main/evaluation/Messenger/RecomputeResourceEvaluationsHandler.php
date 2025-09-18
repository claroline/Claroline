<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\RecomputeResourceEvaluations;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Recompute WorkspaceEvaluations for a Workspace and a list of Users.
 */
#[AsMessageHandler]
readonly class RecomputeResourceEvaluationsHandler
{
    public function __construct(
        private ObjectManager $om,
        private ResourceEvaluationManager $evaluationManager
    ) {
    }

    public function __invoke(RecomputeResourceEvaluations $initMessage): void
    {
        $resourceNode = $this->om->getRepository(ResourceNode::class)->find($initMessage->getResourceId());
        if (empty($resourceNode) || !$this->evaluationManager->supportsEvaluation($resourceNode)) {
            return;
        }

        $evaluations = $this->om->getRepository(ResourceEvaluation::class)->findInProgress($resourceNode);

        $this->om->startFlushSuite();

        foreach ($evaluations as $i => $evaluation) {
            $this->evaluationManager->refreshEvaluation($evaluation);

            if (0 === $i % 200) {
                $this->om->forceFlush();
            }
        }

        $this->om->endFlushSuite();
    }
}

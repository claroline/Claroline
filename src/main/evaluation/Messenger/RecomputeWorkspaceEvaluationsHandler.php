<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\UserEvaluation\WorkspaceEvaluation;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\RecomputeWorkspaceEvaluations;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Recompute WorkspaceEvaluations for a Workspace and a list of Users.
 */
#[AsMessageHandler]
readonly class RecomputeWorkspaceEvaluationsHandler
{
    public function __construct(
        private ObjectManager $om,
        private WorkspaceEvaluationManager $evaluationManager
    ) {
    }

    public function __invoke(RecomputeWorkspaceEvaluations $initMessage): void
    {
        $workspace = $this->om->getRepository(Workspace::class)->find($initMessage->getWorkspaceId());
        if (empty($workspace)) {
            return;
        }

        $evaluations = $this->om->getRepository(WorkspaceEvaluation::class)->findBy(['workspace' => $workspace]);

        $this->om->startFlushSuite();

        foreach ($evaluations as $i => $evaluation) {
            $this->evaluationManager->recomputeEvaluation($evaluation);

            if (0 === $i % 200) {
                $this->om->forceFlush();
            }
        }

        $this->om->endFlushSuite();
    }
}

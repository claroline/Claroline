<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Manager\SequenceEvaluationManager;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\InitializeWorkspaceEvaluations;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class InitializeWorkspaceEvaluationsHandler
{
    public function __construct(
        private ObjectManager $om,
        private WorkspaceEvaluationManager $evaluationManager,
        private SequenceEvaluationManager $sequenceEvaluationManager
    ) {
    }

    public function __invoke(InitializeWorkspaceEvaluations $initMessage): void
    {
        $workspace = $this->om->getRepository(Workspace::class)->find($initMessage->getWorkspaceId());
        if (empty($workspace)) {
            return;
        }

        $users = [];
        foreach ($initMessage->getUserIds() as $userId) {
            $user = $this->om->getRepository(User::class)->find($userId);
            if (!empty($user)) {
                $users[] = $user;
            }
        }

        $this->om->startFlushSuite();

        // initialize workspace evaluations
        foreach ($users as $user) {
            $this->evaluationManager->getUserEvaluation($workspace, $user, true);
        }

        $this->om->endFlushSuite();

        // initialize evaluations for sequences
        // the code does not require this, but is a feature for managers to see users in evaluation tool/exports
        // event if they have not opened the workspace yet.
        $sequences = $this->om->getRepository(Sequence::class)->findBy([
            'published' => true,
            'workspace' => $workspace,
        ]);
        if (!empty($sequences)) {
            foreach ($sequences as $sequence) {
                $this->sequenceEvaluationManager->initializeEvaluations($sequence);
            }
        }
    }
}

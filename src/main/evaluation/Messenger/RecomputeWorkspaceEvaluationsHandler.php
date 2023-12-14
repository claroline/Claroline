<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\RecomputeWorkspaceEvaluations;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

/**
 * Recompute WorkspaceEvaluations for a Workspace and a list of Users.
 */
class RecomputeWorkspaceEvaluationsHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly ObjectManager $om,
        private readonly WorkspaceEvaluationManager $evaluationManager
    ) {
    }

    public function __invoke(RecomputeWorkspaceEvaluations $initMessage): void
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

        foreach ($users as $i => $user) {
            $this->evaluationManager->computeEvaluation($workspace, $user);

            if (0 === $i % 200) {
                $this->om->forceFlush();
            }
        }

        $this->om->endFlushSuite();
    }
}

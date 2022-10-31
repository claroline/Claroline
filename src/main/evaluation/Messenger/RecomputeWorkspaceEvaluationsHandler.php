<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\RecomputeWorkspaceEvaluations;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class RecomputeWorkspaceEvaluationsHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var WorkspaceEvaluationManager */
    private $evaluationManager;

    public function __construct(
        ObjectManager $om,
        WorkspaceEvaluationManager $evaluationManager
    ) {
        $this->om = $om;
        $this->evaluationManager = $evaluationManager;
    }

    public function __invoke(RecomputeWorkspaceEvaluations $initMessage)
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
            $evaluation = $this->evaluationManager->computeEvaluation($workspace, $user);
            if ($evaluation) {
                $this->evaluationManager->computeDuration($evaluation);
            }

            if (0 === $i % 200) {
                $this->om->forceFlush();
            }
        }

        $this->om->endFlushSuite();
    }
}

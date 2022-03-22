<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Manager\WorkspaceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\InitializeResourceEvaluations;
use Claroline\EvaluationBundle\Messenger\Message\InitializeWorkspaceEvaluations;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class InitializeWorkspaceEvaluationsHandler implements MessageHandlerInterface
{
    /** @var MessageBusInterface */
    private $messageBus;
    /** @var ObjectManager */
    private $om;
    /** @var WorkspaceEvaluationManager */
    private $evaluationManager;

    public function __construct(
        MessageBusInterface $messageBus,
        ObjectManager $om,
        WorkspaceEvaluationManager $evaluationManager
    ) {
        $this->messageBus = $messageBus;
        $this->om = $om;
        $this->evaluationManager = $evaluationManager;
    }

    public function __invoke(InitializeWorkspaceEvaluations $initMessage)
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

        // initialize evaluations for required resources
        // this is not required by the code, but is a feature for managers to see users in evaluation tool/exports
        // event if they have not opened the workspace yet.
        $requiredResources = $this->evaluationManager->getRequiredResources($workspace);
        foreach ($requiredResources as $requiredResource) {
            $this->messageBus->dispatch(new InitializeResourceEvaluations($requiredResource->getId(), $initMessage->getUserIds(), AbstractEvaluation::STATUS_TODO));
        }

        $this->om->endFlushSuite();
    }
}

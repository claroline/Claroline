<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Claroline\EvaluationBundle\Manager\SequenceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\InitializeSequenceEvaluations;
use Claroline\EvaluationBundle\Messenger\Message\UpdateResourceEvaluations;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
readonly class InitializeSequenceEvaluationsHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private ObjectManager $om,
        private SequenceEvaluationManager $evaluationManager
    ) {
    }

    public function __invoke(InitializeSequenceEvaluations $initMessage): void
    {
        $sequence = $this->om->getRepository(Sequence::class)->find($initMessage->getSequenceId());
        if (empty($sequence)) {
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
            $this->evaluationManager->getUserEvaluation($sequence, $user, true);
        }

        $this->om->endFlushSuite();

        // initialize evaluations for required resources
        // the code does not require this, but it is a feature for managers to see users in evaluation tool/exports
        // event if they have not opened the sequence yet.
        $requiredResources = $this->evaluationManager->getRequiredResources($sequence);
        foreach ($requiredResources as $requiredResource) {
            $this->messageBus->dispatch(new UpdateResourceEvaluations($requiredResource->getId(), $initMessage->getUserIds()));
        }
    }
}

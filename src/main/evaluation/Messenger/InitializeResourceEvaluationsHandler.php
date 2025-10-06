<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\InitializeResourceEvaluations;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class InitializeResourceEvaluationsHandler
{
    public function __construct(
        private ObjectManager $om,
        private ResourceEvaluationManager $resourceEvaluationManager
    ) {
    }

    public function __invoke(InitializeResourceEvaluations $initMessage): void
    {
        $resourceNode = $this->om->getRepository(ResourceNode::class)->find($initMessage->getResourceNodeId());
        if (empty($resourceNode)) {
            return;
        }

        $this->om->startFlushSuite();

        foreach ($initMessage->getUserIds() as $userId) {
            $user = $this->om->getRepository(User::class)->find($userId);
            $this->resourceEvaluationManager->getUserEvaluation($resourceNode, $user, true);
        }

        $this->om->endFlushSuite();
    }
}

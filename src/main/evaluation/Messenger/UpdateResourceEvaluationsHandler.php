<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\EvaluationBundle\Messenger\Message\UpdateResourceEvaluations;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdateResourceEvaluationsHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var ResourceEvaluationManager */
    private $resourceEvaluationManager;

    public function __construct(
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvaluationManager
    ) {
        $this->om = $om;
        $this->resourceEvaluationManager = $resourceEvaluationManager;
    }

    public function __invoke(UpdateResourceEvaluations $initMessage)
    {
        $resourceNode = $this->om->getRepository(ResourceNode::class)->find($initMessage->getResourceNodeId());
        if (empty($resourceNode)) {
            return;
        }

        $users = [];
        foreach ($initMessage->getUserIds() as $userId) {
            $user = $this->om->getRepository(User::class)->find($userId);
            if (!empty($user)) {
                $users[] = $user;
            }
        }

        foreach ($users as $user) {
            // update method will create the evaluation if missing
            // it also dispatches the evaluation event to let the workspace evaluation update
            // (this is useless when the message is dispatched by the InitializeWorkspaceEvaluations)
            // we can not do it into a flush suite because the workspace calculation requires the info to be persisted in db
            $this->resourceEvaluationManager->updateUserEvaluation(
                $resourceNode,
                $user,
                ['status' => $initMessage->getStatus()],
                null,
                $initMessage->getWithCreation()
            );
        }
    }
}

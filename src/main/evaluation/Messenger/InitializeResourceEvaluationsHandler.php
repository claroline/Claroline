<?php

namespace Claroline\EvaluationBundle\Messenger;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;
use Claroline\EvaluationBundle\Messenger\Message\InitializeResourceEvaluations;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class InitializeResourceEvaluationsHandler implements MessageHandlerInterface
{
    /** @var ObjectManager */
    private $om;

    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;
    }

    public function __invoke(InitializeResourceEvaluations $initMessage)
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

        $status = $initMessage->getStatus();

        $this->om->startFlushSuite();

        foreach ($users as $user) {
            $resourceUserEval = $this->om->getRepository(ResourceUserEvaluation::class)->findOneBy([
                'resourceNode' => $resourceNode,
                'user' => $user,
            ]);

            if (!$resourceUserEval) {
                $resourceUserEval = new ResourceUserEvaluation();
                $resourceUserEval->setResourceNode($resourceNode);
                $resourceUserEval->setUser($user);
            }

            if (AbstractEvaluation::STATUS_PRIORITY[$status] >= AbstractEvaluation::STATUS_PRIORITY[$resourceUserEval->getStatus()]) {
                $resourceUserEval->setStatus($status);
            }

            $this->om->persist($resourceUserEval);
        }

        $this->om->endFlushSuite();
    }
}

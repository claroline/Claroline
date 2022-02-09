<?php

namespace Claroline\EvaluationBundle\Messenger\Message;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;

class InitializeResourceEvaluations
{
    /** @var ResourceNode */
    private $resourceNode;
    /** @var User[] */
    private $users;
    /** @var string */
    private $status;

    public function __construct(ResourceNode $resourceNode, array $users, string $status = AbstractEvaluation::STATUS_NOT_ATTEMPTED)
    {
        $this->resourceNode = $resourceNode;
        $this->users = $users;
        $this->status = $status;
    }

    public function getResourceNode(): ResourceNode
    {
        return $this->resourceNode;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}

<?php

namespace Claroline\EvaluationBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;
use Claroline\EvaluationBundle\Entity\AbstractEvaluation;

class InitializeResourceEvaluations implements AsyncMessageInterface
{
    /** @var int */
    private $resourceNodeId;
    /** @var int[] */
    private $userIds;
    /** @var string */
    private $status;

    public function __construct(int $resourceNodeId, array $userIds, string $status = AbstractEvaluation::STATUS_NOT_ATTEMPTED)
    {
        $this->resourceNodeId = $resourceNodeId;
        $this->userIds = $userIds;
        $this->status = $status;
    }

    public function getResourceNodeId(): int
    {
        return $this->resourceNodeId;
    }

    public function getUserIds(): array
    {
        return $this->userIds;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}

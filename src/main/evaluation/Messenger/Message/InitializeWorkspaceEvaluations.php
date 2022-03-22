<?php

namespace Claroline\EvaluationBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class InitializeWorkspaceEvaluations implements AsyncMessageInterface
{
    /** @var int */
    private $workspaceId;
    /** @var int[] */
    private $userIds;

    public function __construct(int $workspaceId, array $userIds)
    {
        $this->workspaceId = $workspaceId;
        $this->userIds = $userIds;
    }

    public function getWorkspaceId(): int
    {
        return $this->workspaceId;
    }

    public function getUserIds(): array
    {
        return $this->userIds;
    }
}

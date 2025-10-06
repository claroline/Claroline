<?php

namespace Claroline\EvaluationBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncHighMessageInterface;

final readonly class InitializeResourceEvaluations implements AsyncHighMessageInterface
{
    public function __construct(
        private int $resourceNodeId,
        private array $userIds
    ) {
    }

    public function getResourceNodeId(): int
    {
        return $this->resourceNodeId;
    }

    public function getUserIds(): array
    {
        return $this->userIds;
    }
}

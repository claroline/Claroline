<?php

namespace Claroline\EvaluationBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncHighMessageInterface;

final readonly class InitializeSequenceEvaluations implements AsyncHighMessageInterface
{
    public function __construct(
        private int $sequenceId,
        private array $userIds
    ) {
    }

    public function getSequenceId(): int
    {
        return $this->sequenceId;
    }

    public function getUserIds(): array
    {
        return $this->userIds;
    }
}

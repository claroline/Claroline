<?php

namespace Claroline\OpenBadgeBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncHighMessageInterface;

class GrantRule implements AsyncHighMessageInterface
{
    public function __construct(
        private readonly int $ruleId,
        private readonly int $userId
    ) {
    }

    public function getRuleId(): int
    {
        return $this->ruleId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}

<?php

namespace Claroline\OpenBadgeBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncMessageInterface;

class GrantRule implements AsyncMessageInterface
{
    /** @var int */
    private $ruleId;

    /** @var int */
    private $userId;

    public function __construct(int $ruleId, int $userId)
    {
        $this->ruleId = $ruleId;
        $this->userId = $userId;
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

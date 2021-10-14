<?php

namespace Claroline\OpenBadgeBundle\Messenger\Message;

class GrantRule
{
    /** @var string */
    private $ruleId;

    /** @var string */
    private $userId;

    public function __construct(string $ruleId, string $userId)
    {
        $this->ruleId = $ruleId;
        $this->userId = $userId;
    }

    public function getRuleId(): string
    {
        return $this->ruleId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }
}

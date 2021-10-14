<?php

namespace Claroline\OpenBadgeBundle\Messenger\Message;

class GrantBadge
{
    /** @var string */
    private $badgeId;

    public function __construct(string $badgeId)
    {
        $this->badgeId = $badgeId;
    }

    public function getBadgeId(): string
    {
        return $this->badgeId;
    }
}

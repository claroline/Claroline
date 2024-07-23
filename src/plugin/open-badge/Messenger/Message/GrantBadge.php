<?php

namespace Claroline\OpenBadgeBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncHighMessageInterface;

class GrantBadge implements AsyncHighMessageInterface
{
    public function __construct(
        private readonly int $badgeId
    ) {
    }

    public function getBadgeId(): int
    {
        return $this->badgeId;
    }
}

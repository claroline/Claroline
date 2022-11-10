<?php

namespace Claroline\OpenBadgeBundle\Messenger\Message;

use Claroline\AppBundle\Messenger\Message\AsyncHighMessageInterface;

class GrantBadge implements AsyncHighMessageInterface
{
    /** @var int */
    private $badgeId;

    public function __construct(int $badgeId)
    {
        $this->badgeId = $badgeId;
    }

    public function getBadgeId(): int
    {
        return $this->badgeId;
    }
}

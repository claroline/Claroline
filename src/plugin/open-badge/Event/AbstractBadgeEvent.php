<?php

namespace Claroline\OpenBadgeBundle\Event;

use Claroline\CoreBundle\Entity\User;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractBadgeEvent extends Event
{
    public function __construct(
        private readonly User $user,
        private readonly BadgeClass $badge
    ) {
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getBadge(): BadgeClass
    {
        return $this->badge;
    }
}

<?php

namespace Claroline\OpenBadgeBundle\Event;

final class BadgeEvents
{
    /**
     * @Event("Claroline\OpenBadgeBundle\Event\AddBadgeEvent")
     */
    public const BADGE_ADD = 'badge_add';

    /**
     * @Event("Claroline\OpenBadgeBundle\Event\RemoveBadgeEvent")
     */
    public const BADGE_REMOVE = 'badge_remove';
}

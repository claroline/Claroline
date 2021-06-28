<?php

namespace Claroline\OpenBadgeBundle\Event;

final class BadgeEvents
{
    /**
     * @Event("Claroline\OpenBadgeBundle\Event\AddBadgeEvent")
     */
    public const ADD_BADGE = 'badge_add';

    /**
     * @Event("Claroline\OpenBadgeBundle\Event\RemoveBadgeEvent")
     */
    public const REMOVE_BADGE = 'badge_remove';
}

<?php

namespace Claroline\OpenBadgeBundle\Event;

final class BadgeEvents
{
    /**
     * @Event("Claroline\OpenBadgeBundle\Event\AddBadgeEvent")
     */
    public const ADD_BADGE = 'event.funcitonal.add_badge';

    /**
     * @Event("Claroline\OpenBadgeBundle\Event\RemoveBadgeEvent")
     */
    public const REMOVE_BADGE = 'event.funcitonal.remove_badge';
}

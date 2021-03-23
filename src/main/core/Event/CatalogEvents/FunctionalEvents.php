<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class FunctionalEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Functional\AddBadgeEvent")
     */
    public const ADD_BADGE = 'event.funcitonal.add_badge';

    /**
     * @Event("Claroline\CoreBundle\Event\Functional\RemoveBadgeEvent")
     */
    public const REMOVE_BADGE = 'event.funcitonal.remove_badge';

    public const RESOURCE_ENTERING = 'event.functional.resource_entering';

    public const RESOURCE_EXITING = 'event.functional.resource_exiting';
}

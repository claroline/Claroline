<?php

namespace Claroline\AppBundle\Event;

use Claroline\AppBundle\Event\Finder\BuildQueryEvent;

/**
 * Events fired during the rendering of the Web client.
 */
final class FinderEvents
{
    /**
     * The BUILD_QUERY event occurs when the Finder processes a user query and generate the resulting DB query.
     *
     * This event allows you to modify the query before it is executed.
     *
     * @Event("Claroline\AppBundle\Event\Finder\BuildQueryEvent")
     */
    public const BUILD_QUERY = 'finder.build_query';

    /**
     * Event aliases.
     */
    public const ALIASES = [
        BuildQueryEvent::class => self::BUILD_QUERY,
    ];

    private function __construct()
    {
    }
}

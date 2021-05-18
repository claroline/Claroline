<?php

namespace Claroline\LogBundle\Event\CatalogEvents;

final class ToolEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Tool\OpenToolEvent")
     */
    public const TOOL_OPEN = 'tool_open';
}

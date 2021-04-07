<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class ToolEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Tool\OpenToolEvent")
     */
    public const TOOL_OPEN = 'event.functional.tool_open';
}

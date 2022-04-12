<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class ToolEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Tool\OpenToolEvent")
     */
    public const OPEN = 'tool.open';

    /**
     * @Event("Claroline\CoreBundle\Event\Tool\CloseToolEvent")
     */
    public const CLOSE = 'tool.close';

    /**
     * @Event("Claroline\CoreBundle\Event\Tool\ConfigureToolEvent")
     */
    public const CONFIGURE = 'tool.configure';

    /**
     * @Event("Claroline\CoreBundle\Event\Tool\ExportToolEvent")
     */
    public const EXPORT = 'tool.export';

    /**
     * @Event("Claroline\CoreBundle\Event\Tool\ImportToolEvent")
     */
    public const IMPORT = 'tool.import';

    public static function getEventName(string $event, string $context, string $toolName): string
    {
        return $event.'.'.$context.'.'.$toolName;
    }
}

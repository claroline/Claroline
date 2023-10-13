<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class ContextEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\Context\OpenContextEvent")
     */
    public const OPEN = 'context.open';

    /**
     * @Event("Claroline\CoreBundle\Event\Context\CloseContextEvent")
     */
    public const CLOSE = 'context.close';
}

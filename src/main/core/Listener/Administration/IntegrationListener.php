<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;

class IntegrationListener
{
    /**
     * Displays integration administration tool.
     *
     * @param OpenToolEvent $event
     */
    public function onDisplayTool(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}

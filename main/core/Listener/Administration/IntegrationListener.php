<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Event\Tool\OpenToolEvent;

class IntegrationListener
{
    /**
     * Displays analytics administration tool.
     *
     * @param OpenToolEvent $event
     */
    public function onDisplayTool(OpenToolEvent $event)
    {
        $event->setData([]);
        $event->stopPropagation();
    }
}

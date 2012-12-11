<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Claroline\CoreBundle\Library\Resource\Mode;

/**
 * If a "_mode" parameter is passed in the request with a "path" value,
 * this listener the resource mode flag to true.
 */
class ModeSelector
{
    /**
     * Sets the resource mode.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $mode = $event->getRequest()->get('_mode');

        if ($mode === 'path') {
            Mode::$isPathMode = true;
        }
    }
}
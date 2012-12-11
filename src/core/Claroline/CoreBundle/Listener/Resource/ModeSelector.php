<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Claroline\CoreBundle\Library\Resource\Mode;

/**
 * If a "_mode" parameter is passed in the request with a "path" value, this listener
 * sets the resource mode accordingly, i.e. sets the path mode flag and override the
 * default resource template (workspace::layout.html.twig)
 */
class ModeSelector
{
    /**
     * Sets the resource mode
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $mode = $event->getRequest()->get('_mode');

        if ($mode === 'path') {
            Mode::$isPathMode = true;
            Mode::$template = 'ClarolineCoreBundle:Resource:path_layout.html.twig';
        }
    }
}
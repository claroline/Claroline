<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Resource\Mode;

/**
 * @DI\Service
 *
 * If a "_mode" parameter is passed in the request with a "path" value,
 * this listener the resource mode flag to true.
 */
class ModeSelector
{
    /**
     * @DI\Observe("kernel.request")
     *
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

    /**
     * @DI\Observe("kernel.response")
     *
     * Appends the mode to the url if needed.
     *
     * @param $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (($response = $event->getResponse()) instanceof RedirectResponse && Mode::$isPathMode) {
            $response->setTargetUrl($response->getTargetUrl() . '?_mode=path');
        }
    }
}
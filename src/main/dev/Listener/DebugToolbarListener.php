<?php

namespace Claroline\DevBundle\Listener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class DebugToolbarListener
{
    public function __construct(
        private readonly bool $debug
    ) {
    }

    /**
     * Auto refreshes the symfony debug toolbar on ajax requests.
     *
     * @see https://symfony.com/doc/4.4/profiler.html#updating-the-web-debug-toolbar-after-ajax-requests
     */
    public function onResponse(ResponseEvent $event): void
    {
        if (!$this->debug) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->isXmlHttpRequest()) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Symfony-Debug-Toolbar-Replace', 1);
    }
}

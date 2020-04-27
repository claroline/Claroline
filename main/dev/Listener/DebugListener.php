<?php

namespace Claroline\DevBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class DebugListener
{
    /**
     * If we're returning a JsonResponse, we can get the debug bar by passing ?debug=true on the query string.
     *
     * @param FilterResponseEvent $event
     */
    public function onResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $debug = $request->query->get('debug');
        $response = $event->getResponse();

        //this is for debug purpose
        if ($response instanceof JsonResponse && $debug) {
            $new = new Response();
            $new->setContent('<body>'.$response->getContent().'</body>');
            $event->setResponse($new);
        }
    }
}

<?php

namespace Claroline\AppBundle\API\Listener;

use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @DI\Service()
 * Move this somewhere else
 */
class ApiListener
{
    /**
     * Converts Exceptions in JSON for the async api.
     *
     * @DI\Observe("kernel.exception", priority=99)
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onError(GetResponseForExceptionEvent $event)
    {
        if ($event->getRequest()->isXmlHttpRequest()) {
            // return json response with errors details
            $exception = $event->getException();
            if ($exception instanceof InvalidDataException) {
                // return correct status code for invalid data sent by the user
                $response = new JsonResponse($exception->getErrors(), 422);
            } else {
                $response = new JsonResponse([
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace(),
                ], 500);
            }

            $event->setResponse($response);
        }
    }

    /**
     * If we're returning a JsonResponse, we can get the debug bar by passing ?debug=true on the query string.
     *
     * @DI\Observe("kernel.response")
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

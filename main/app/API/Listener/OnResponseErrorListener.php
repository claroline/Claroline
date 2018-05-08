<?php

namespace Claroline\AppBundle\API\Listener;

use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * @DI\Service()
 * Move this somewhere else
 */
class OnResponseErrorListener
{
    /**
     * Converts Exceptions in JSON for the async api.
     *
     * @DI\Observe("kernel.exception", priority=99)
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function handleError(GetResponseForExceptionEvent $event)
    {
        if ($event->getRequest()->isXmlHttpRequest()) {
            $exception = $event->getException();
            if ($exception instanceof InvalidDataException) {
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
}

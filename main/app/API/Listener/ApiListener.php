<?php

namespace Claroline\AppBundle\API\Listener;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Move this somewhere else.
 */
class ApiListener
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * ApiListener constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(
        TokenStorageInterface $tokenStorage
    ) {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Converts Exceptions in JSON for the async api.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onError(GetResponseForExceptionEvent $event)
    {
        $user = null;
        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        $exception = $event->getException();
        if ($exception instanceof AccessDeniedException && !$user instanceof User) {
            // FIXME : this is really ugly to handle it here but I can't get the firewall returns 401 for anonymous
            // it always returns me 403 which is not correct in this case
            $response = new JsonResponse($exception->getMessage(), 401);

            $event->setResponse($response);
        } elseif ($exception instanceof InvalidDataException) {
            // return correct status code for invalid data sent by the user
            $response = new JsonResponse($exception->getErrors(), 422);

            $event->setResponse($response);
        } elseif ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse($exception->getMessage(), 404);

            $event->setResponse($response);
        } else {
            if ($event->getRequest()->isXmlHttpRequest()) {
                $response = new JsonResponse([
                    'message' => $exception->getMessage(),
                    'trace' => $exception->getTrace(),
                    //if <200, not http error code
                ], $exception->getCode() < 200 ? 500 : $exception->getCode());

                $event->setResponse($response);
            }
        }
    }

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

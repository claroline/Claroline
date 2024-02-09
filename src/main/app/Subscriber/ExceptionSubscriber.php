<?php

namespace Claroline\AppBundle\Subscriber;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Converts applications exceptions into JSON responses for the API.
 */
class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly bool $debug,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 99],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $user = null;
        if ($this->tokenStorage->getToken()) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        $exception = $event->getThrowable();
        if ($exception instanceof AccessDeniedException && !$user instanceof User) {
            // FIXME : this is really ugly to handle it here but I can't get the firewall returns 401 for anonymous
            // it always returns me 403 which is not correct in this case
            $response = new JsonResponse($exception->getMessage(), 401);

            $event->setResponse($response);
        } elseif ($exception instanceof InvalidDataException) {
            // return correct status code for invalid data sent by the user
            $response = new JsonResponse(!empty($exception->getErrors()) ? $exception->getErrors() : $exception->getMessage(), 422);

            $event->setResponse($response);
        } elseif ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse($exception->getMessage(), 404);

            $event->setResponse($response);
        } elseif ($exception instanceof HttpException) {
            $response = new JsonResponse($exception->getMessage(), $exception->getStatusCode());

            $event->setResponse($response);
        } else {
            if ($event->getRequest()->isXmlHttpRequest()) {
                $response = new JsonResponse([
                    'message' => $exception->getMessage(),
                    'trace' => $this->debug ? $exception->getTrace() : [],
                ], $exception->getCode() < 100 || $exception->getCode() >= 600 ? 500 : $exception->getCode());

                $event->setResponse($response);
            }
        }
    }
}

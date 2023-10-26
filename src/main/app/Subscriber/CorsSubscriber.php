<?php

namespace Claroline\AppBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CorsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 250],
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // skip if not a CORS request
        if (!$request->headers->has('Origin')) {
            return;
        }

        // perform preflight checks
        // It is an OPTIONS request, using two or three HTTP request headers: Access-Control-Request-Method, Origin, and optionally Access-Control-Request-Headers.
        if ('OPTIONS' === $request->getMethod() && $request->headers->has('Access-Control-Request-Method')) {
            $event->setResponse($this->getPreflightResponse($request));

            return;
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // skip if not a CORS request
        if (!$request->headers->has('Origin')) {
            return;
        }

        $response = $event->getResponse();

        // add CORS response headers
        $origin = $request->headers->get('Origin');
        // TODO : check origin is authorized
        $response->headers->set('Access-Control-Allow-Origin', $origin);
    }

    protected function getPreflightResponse(Request $request): Response
    {
        $response = new Response();
        $response->setVary(['Origin']);

        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', 3600);

        $origin = $request->headers->get('Origin');
        // TODO : check origin is authorized
        $response->headers->set('Access-Control-Allow-Origin', $origin);

        return $response;
    }
}

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

        if (!$this->isCorsRequest($request)) {
            // skip if not a CORS request
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

        if (!$this->isCorsRequest($request)) {
            // skip if not a CORS request
            return;
        }

        if ($this->checkOrigin($request)) {
            $response = $event->getResponse();

            // add CORS response headers
            $origin = $request->headers->get('Origin');
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
    }

    /**
     * Generates Response for CORS preflight Requests.
     *
     * @see https://developer.mozilla.org/en-US/docs/Glossary/Preflight_request
     */
    private function getPreflightResponse(Request $request): Response
    {
        $response = new Response();
        $response->setVary(['Origin']);

        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', 3600);
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');

        if (!$this->checkOrigin($request)) {
            $response->headers->remove('Access-Control-Allow-Origin');

            return $response;
        }

        $origin = $request->headers->get('Origin');
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET,POST,PUT,DELETE,PATCH,OPTIONS');

        return $response;
    }

    private function isCorsRequest(Request $request): bool
    {
        return $request->headers->has('Origin')
            && $request->headers->get('Origin') !== $request->getSchemeAndHttpHost();
    }

    private function checkOrigin(Request $request): bool
    {
        // check origin
        $origin = $request->headers->get('Origin');

        if (!in_array($origin, [

        ])) {
            return false;
        }

        return true;
    }
}

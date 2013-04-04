<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class AjaxAuthenticationListener
{
    private $securityContext;

    /**
     * @DI\InjectParams({
     *     "context" = @DI\Inject("security.context")
     * })
     *
     * @param SecurityContextInterface $context
     */
    public function __construct(SecurityContextInterface $context)
    {
        $this->securityContext = $context;
    }

    /**
     * @DI\Observe("kernel.exception", priority = 1)
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        // TODO : find a better way to handle security errors on ajax requests...
        // (see http://stackoverflow.com/questions/8607212/symfony2-ajax-login)
        if ($event->getRequest()->isXmlHttpRequest()
            && ($exception instanceof AccessDeniedHttpException || $exception instanceof AuthenticationException)) {
            $msg = $this->securityContext->getToken() instanceof AnonymousToken ?
                'Anonymous users are not allowed to perform this action (you may have lost your session) '
                . ' : refresh the page to proceed to authentication' :
                'Not allowed';
            $event->setResponse(new Response($msg, 403));
        }
    }
}


<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class AjaxAuthenticationListener extends ContainerAware
{
    /**
     * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        // TODO : find a better way to handle security errors on ajax requests...
        // (see http://stackoverflow.com/questions/8607212/symfony2-ajax-login)
        if ($event->getRequest()->isXmlHttpRequest()
            && ($exception instanceof AccessDeniedHttpException || $exception instanceof AuthenticationException)) {
            $msg = $this->container->get('security.context')->getToken() instanceof AnonymousToken ?
                'Anonymous users are not allowed to perform this action (you may have lost your session) '
                . ' : refresh the page to proceed to authentication' :
                'Not allowed';
            $event->setResponse(new Response($msg, 403));
        }
    }
}


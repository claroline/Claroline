<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
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
     *     "context" = @DI\Inject("security.context"),
     *     "templating" = @DI\Inject("templating"),
     *     "session"= @DI\Inject("session")
     * })
     *
     * @param SecurityContextInterface $context
     */
    public function __construct(SecurityContextInterface $context, $templating, $session)
    {
        $this->securityContext = $context;
        $this->templating = $templating;
        $this->session = $session;
    }

    /**
     * @DI\Observe("kernel.exception", priority = 1)
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()
            && get_class($exception) == 'Symfony\Component\Security\Core\Exception\AccessDeniedException') {
            $form = $this->templating->render(
                'ClarolineCoreBundle:Authentication:loginAjaxForm.html.twig'
            );

            //@see https://github.com/gillest/HackSessionBundle
            if (!$request->hasPreviousSession()) {
                $request->cookies->set(session_name(), 'tmp');
                $request->setSession($this->session);
            }

            $event->setResponse(new Response($form, 403));
        }
    }
}

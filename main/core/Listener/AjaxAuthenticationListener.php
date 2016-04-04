<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class AjaxAuthenticationListener
{
    /**
     * @DI\InjectParams({
     *     "templating" = @DI\Inject("templating"),
     *     "session"= @DI\Inject("session")
     * })
     *
     * @param SecurityContextInterface $context
     */
    public function __construct($templating, $session)
    {
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

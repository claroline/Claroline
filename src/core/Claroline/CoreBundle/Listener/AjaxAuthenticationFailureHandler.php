<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service(
 *     "claroline.security.ajax_authentication_failure_handler",
 *     parent="security.authentication.failure_handler"
 * )
 */
class AjaxAuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $json = array(
                'has_error' => true,
                'error' => $exception->getMessage()
            );

            return new JsonResponse($json);
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}
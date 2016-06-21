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
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $json = array(
                'has_error' => true,
                'error' => $exception->getMessage(),
            );

            return new JsonResponse($json);
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}

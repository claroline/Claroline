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

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

class AuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($exception->getMessage(), 400);
        }

        return parent::onAuthenticationFailure($request, $exception);
    }
}

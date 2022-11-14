<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * Author: Panagiotis TSAVDARIS
 *
 * Date: 7/6/15
 */

namespace Claroline\AuthenticationBundle\Security\Oauth;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    /**
     * @var Router
     */
    private $router;
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * FailureHandler constructor.
     */
    public function __construct(Router $router, TranslatorInterface $translator)
    {
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if (!$exception instanceof UsernameNotFoundException) {
            $msg = $this->translator->trans(
                'error_connecting_with_oauth',
                ['%msg%' => $exception->getMessage()],
                'oauth'
            );
            $request
                ->getSession()
                ->getFlashBag()
                ->add('error', $msg);

            return new RedirectResponse($this->router->generate('claro_security_login'));
        } else {
            return new RedirectResponse($this->router->generate('claro_oauth_check_connexion'));
        }
    }
}

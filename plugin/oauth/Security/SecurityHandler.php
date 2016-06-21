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

namespace Icap\OAuthBundle\Security;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

/**
 * @DI\Service("icap.oauth.security_handler")
 */
class SecurityHandler  implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @DI\InjectParams({
     *   "router"      = @DI\Inject("router")
     * })
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * This is called when an interactive authentication attempt fails. This is
     * called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if (!$exception instanceof UsernameNotFoundException) {
            // Edit it to meet your requeriments
            $request->getSession()->set('login_error', 'error_connecting_with_oauth');

            return new RedirectResponse($this->router->generate('claro_security_login'));
        } else {
            return new RedirectResponse($this->router->generate('icap_oauth_check_connexion'));
        }
    }

    /**
     * This is called when an interactive authentication attempt succeeds. This
     * is called by authentication listeners inheriting from
     * AbstractAuthenticationListener.
     *
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return Response never null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $referer = $request->headers->get('referer');
        if (empty($referer)) {
            return new RedirectResponse($this->router->generate('claro_desktop_open_tool', array('toolName' => 'home')));
        } else {
            return new RedirectResponse($referer);
        }
    }
}

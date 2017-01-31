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
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("icap.oauth.failure_handler")
 */
class FailureHandler implements AuthenticationFailureHandlerInterface
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
     *
     * @DI\InjectParams({
     *     "router"     = @DI\Inject("router"),
     *     "translator" = @DI\Inject("translator")
     * })
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
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return Response The response to return, never null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if (!$exception instanceof UsernameNotFoundException) {
            $msg = $this->translator->trans(
                'error_connecting_with_oauth',
                ['%msg%' => $exception->getMessage()],
                'icap_oauth'
            );
            $request
                ->getSession()
                ->getFlashBag()
                ->add('error', $msg);

            return new RedirectResponse($this->router->generate('claro_security_login'));
        } else {
            return new RedirectResponse($this->router->generate('icap_oauth_check_connexion'));
        }
    }
}

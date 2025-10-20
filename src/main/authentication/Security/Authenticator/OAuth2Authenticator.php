<?php

namespace Claroline\AuthenticationBundle\Security\Authenticator;

use Claroline\AuthenticationBundle\Manager\OAuthManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\HttpUtils;

class OAuth2Authenticator extends AbstractAuthenticator
{
    private const CHECK_ROUTE = 'claro_security_login_check_oauth2';

    public function __construct(
        private readonly HttpUtils $httpUtils,
        private readonly OAuthManager $oauthManager,
        private readonly ?AuthenticationSuccessHandlerInterface $successHandler = null,
        private readonly ?AuthenticationFailureHandlerInterface $failureHandler = null,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $this->httpUtils->checkRequestPath($request, self::CHECK_ROUTE);
    }

    public function authenticate(Request $request): Passport
    {
        return $this->oauthManager->authenticate($request);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return $this->successHandler?->onAuthenticationSuccess($request, $token);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return $this->failureHandler?->onAuthenticationFailure($request, $exception);
    }
}

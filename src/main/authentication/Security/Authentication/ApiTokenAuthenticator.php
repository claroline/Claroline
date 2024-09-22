<?php

namespace Claroline\AuthenticationBundle\Security\Authentication;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Manages authentication of users with api tokens.
 */
class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public const QUERY_PARAM = 'apitoken';
    public const HEADER_NAME = 'CLAROLINE-API-TOKEN';

    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::HEADER_NAME) || $request->query->has(self::QUERY_PARAM);
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get(self::HEADER_NAME) ?: $request->query->get(self::QUERY_PARAM);
        if (null === $apiToken) {
            throw new AuthenticationCredentialsNotFoundException();
        }

        $token = $this->om->getRepository(ApiToken::class)->findOneBy(['token' => $apiToken]);
        if (!$token) {
            throw new AuthenticationCredentialsNotFoundException();
        }

        return new SelfValidatingPassport(new UserBadge($token->getUser()->getUserIdentifier()));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}

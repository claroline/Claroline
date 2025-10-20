<?php

namespace Claroline\AuthenticationBundle\Security\Authenticator;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\ApiToken;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
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

        // we don't need to pass the `$userLoader` param to UserBadge because it uses the default UserProvider
        // we do it for tests isolation
        return new SelfValidatingPassport(new UserBadge($token->getUser()->getUserIdentifier(), function (string $userIdentifier): ?UserInterface {
            return $this->om->getRepository(User::class)->loadUserByIdentifier($userIdentifier);
        }));
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

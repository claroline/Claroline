<?php

namespace Claroline\AuthenticationBundle\Security\Authentication;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\IpUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Manages authentication of users with white listed IPs.
 */
class IpAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly ObjectManager $om
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return true;
    }

    /**
     * {@inheritdoc}.
     */
    public function authenticate(Request $request): Passport
    {
        $credentials = $request->getClientIp();

        // check if there is a user attached to this ip
        $ipUser = $this->om->getRepository(IpUser::class)->findOneBy(['ip' => $credentials]);
        if ($ipUser) {
            return new SelfValidatingPassport(new UserBadge($ipUser->getUser()->getUserIdentifier()));
        }

        // check ip ranges
        $ranges = $this->om->getRepository(IpUser::class)->findBy(['range' => true]);
        foreach ($ranges as $range) {
            if ($range->inRange($credentials)) {
                return new SelfValidatingPassport(new UserBadge($range->getUser()->getUserIdentifier()));
            }
        }

        throw new AuthenticationCredentialsNotFoundException();
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

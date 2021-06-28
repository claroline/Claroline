<?php

namespace Claroline\AuthenticationBundle\Security\Authentication\Guard;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AuthenticationBundle\Entity\IpUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Manages authentication of users with white listed IPs.
 */
class IpAuthenticator extends AbstractGuardAuthenticator
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * {@inheritdoc}.
     */
    public function supports(Request $request)
    {
        return true;
    }

    /**
     * {@inheritdoc}.
     */
    public function getCredentials(Request $request)
    {
        return $request->getClientIp();
    }

    /**
     * {@inheritdoc}.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // check if there is a user attached to this ip
        $ipUser = $this->om->getRepository(IpUser::class)->findOneBy(['ip' => $credentials]);
        if ($ipUser) {
            return $ipUser->getUser();
        }

        // check ip ranges
        $ranges = $this->om->getRepository(IpUser::class)->findBy(['range' => true]);
        foreach ($ranges as $range) {
            if ($range->inRange($credentials)) {
                return $range->getUser();
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // no-op - nothing to validate
        return true;
    }

    /**
     * {@inheritdoc}.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * {@inheritdoc}.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // let the request continue unauthenticated (anonymous)
        return null;
    }

    /**
     * {@inheritdoc}.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse('/');
    }

    /**
     * {@inheritdoc}.
     */
    public function supportsRememberMe()
    {
        return false;
    }
}

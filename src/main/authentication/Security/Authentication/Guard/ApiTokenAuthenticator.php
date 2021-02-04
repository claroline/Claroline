<?php

namespace Claroline\AuthenticationBundle\Security\Authentication\Guard;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Cryptography\ApiToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Manages authentication of users with api tokens.
 */
class ApiTokenAuthenticator extends AbstractGuardAuthenticator
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
        return $request->query->has('apitoken');
    }

    /**
     * {@inheritdoc}.
     */
    public function getCredentials(Request $request)
    {
        return $request->query->get('apitoken');
    }

    /**
     * {@inheritdoc}.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $apiToken = $this->om->getRepository(ApiToken::class)->findOneBy(['token' => $credentials]);

        if (!$apiToken instanceof ApiToken) {
            return null;
        }

        return $apiToken->getUser();
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

<?php

namespace Claroline\AuthenticationBundle\Security\Authentication\Guard;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\IPWhiteListManager;
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
    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var IPWhiteListManager */
    private $whiteListManager;

    public function __construct(PlatformConfigurationHandler $config, IPWhiteListManager $whiteListManager)
    {
        $this->config = $config;
        $this->whiteListManager = $whiteListManager;
    }

    /**
     * (@inheritdoc}.
     */
    public function supports(Request $request)
    {
        return $this->whiteListManager->isWhiteListed() && !empty($this->config->getParameter('security.default_root_anon_id'));
    }

    /**
     * (@inheritdoc}.
     */
    public function getCredentials(Request $request)
    {
        return $this->config->getParameter('security.default_root_anon_id');
    }

    /**
     * (@inheritdoc}.
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($this->config->getParameter('security.default_root_anon_id'));
    }

    /**
     * (@inheritdoc}.
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        // no-op - nothing to validate
        return true;
    }

    /**
     * (@inheritdoc}.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    /**
     * (@inheritdoc}.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // let the request continue unauthenticated (anonymous)
        return null;
    }

    /**
     * (@inheritdoc}.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse('/');
    }

    /**
     * (@inheritdoc}.
     */
    public function supportsRememberMe()
    {
        return false;
    }
}

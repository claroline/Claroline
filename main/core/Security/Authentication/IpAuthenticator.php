<?php

namespace Claroline\CoreBundle\Security\Authentication;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\IPWhiteListManager;
use Claroline\CoreBundle\Security\Authentication\Token\IpToken;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;

/**
 * Manages authentication of users with white listed IPs.
 *
 * @DI\Service("claroline.security.authentication.ip_authenticator")
 */
class IpAuthenticator implements SimplePreAuthenticatorInterface
{
    /** @var PlatformConfigurationHandler */
    private $config;

    /** @var IPWhiteListManager */
    private $whiteListManager;

    /**
     * IpAuthenticator constructor.
     *
     * @DI\InjectParams({
     *     "config"           = @DI\Inject("claroline.config.platform_config_handler"),
     *     "whiteListManager" = @DI\Inject("claroline.manager.ip_white_list_manager")
     * })
     *
     * @param PlatformConfigurationHandler $config
     * @param IPWhiteListManager           $whiteListManager
     */
    public function inject(
        PlatformConfigurationHandler $config,
        IPWhiteListManager $whiteListManager)
    {
        $this->config = $config;
        $this->whiteListManager = $whiteListManager;
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof IpToken && $token->getProviderKey() === $providerKey;
    }

    public function createToken(Request $request, $providerKey)
    {
        if (!$this->whiteListManager->isWhiteListed()) {
            // skip ip authentication
            return null;
        }

        // initialize a new token for the user
        return new IpToken(
            $this->config->getParameter('default_root_anon_id'),
            null,
            $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $username = $token->getUsername();
        if (!$username) {
            throw new AuthenticationException(
                sprintf('User "%s" for white listed IPs can not be found.', $username)
            );
        }

        $user = $userProvider->loadUserByUsername($username);

        // returns the authenticated token
        return new IpToken(
            $user,
            null,
            $providerKey,
            $user->getRoles()
        );
    }
}

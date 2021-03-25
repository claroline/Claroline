<?php

namespace Claroline\AuthenticationBundle\Security\Oauth;

use Claroline\AuthenticationBundle\Entity\OauthUser;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OauthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    private $em;
    /** @var SessionInterface */
    private $session;
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;

    /**
     * @param $em
     */
    public function __construct(
        $em,
        SessionInterface $session,
        PlatformConfigurationHandler $platformConfigHandler
    ) {
        $this->em = $em;
        $this->session = $session;
        $this->platformConfigHandler = $platformConfigHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->em->getRepository(User::class)->loadUserByUsername($username);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $resourceOwner = $response->getResourceOwner();

        // save the current oauth session token for later use (eg. logout)
        // it is saved in all cases, because even if we don't find the user entity now,
        // the user will be able to register/login and link its claroline account later
        // TODO : this is clearly not the good place to do it
        $this->saveResourceOwnerToken($resourceOwner, $response->getAccessToken());

        // try to find the user with an existing link (OauthUser) between the claroline account and the oauth provider
        $user = $this->loadUserByServiceAndId(
            $resourceOwner->getName(),
            $response->getUsername()
        );

        if (empty($user)) {
            // there is no claroline account linked to this oauth account

            // if the platform allows it, we will try to automatically link this oauth account to
            // an existing claroline account with the same email
            if ($this->platformConfigHandler->getParameter('authentication.direct_third_party')) {
                try {
                    $user = $this->loadUserByUsername($response->getEmail());
                    $this->linkUserToOauth($user, $resourceOwner->getName(), $response->getUsername());
                } catch (UsernameNotFoundException $e) {
                }
            }
        }

        if (empty($user)) {
            // store the current oauth session info, so the user can link its claroline account to it
            // once he logged in or register.
            $this->session->set('claroline.oauth.resource_owner', [
                'name' => $resourceOwner->getName(),
                'id' => $response->getUsername(),
            ]);

            throw new UsernameNotFoundException(sprintf('Unable to find an active user identified by "%s".', $response->getUsername()));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    private function loadUserByServiceAndId(string $service, $id)
    {
        $oauthUser = $this->em->getRepository(OauthUser::class)->findOneBy([
            'service' => $service,
            'oauthId' => $id,
        ]);

        if (!empty($oauthUser)) {
            return $oauthUser->getUser();
        }

        return null;
    }

    private function linkUserToOauth(User $user, string $service, $id)
    {
        $oauthUser = new OauthUser();
        $oauthUser->setUser($user);
        $oauthUser->setService($service);
        $oauthUser->setOauthId($id);

        $this->em->persist($oauthUser);
        $this->em->flush();
    }

    /**
     * Save the current oauth session token. It will be used later to logout user from the oauth provider
     * when he logout from Claroline.
     *
     * @param $resourceOwner
     * @param $token
     */
    private function saveResourceOwnerToken($resourceOwner, $token)
    {
        $this->session->set('claroline.oauth.resource_owner_token', [
            'resourceOwnerName' => $resourceOwner->getName(),
            'token' => $token,
        ]);
    }
}

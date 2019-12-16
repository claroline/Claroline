<?php

namespace Claroline\AuthenticationBundle\Security\Oauth;

use Claroline\AuthenticationBundle\Entity\OauthUser;
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
     * @param SessionInterface             $session
     * @param PlatformConfigurationHandler $platformConfigHandler
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
        return $this->em->getRepository('ClarolineCoreBundle:User')->loadUserByUsername($username);
    }

    public function loadUserByServiceAndId($service, $id, $email = null)
    {
        $oauthUser = $this->em->getRepository(OauthUser::class)->findOneBy(
            ['service' => $service, 'oauthId' => $id]
        );

        if (!empty($oauthUser)) {
            return $oauthUser->getUser();
        }

        if ($this->platformConfigHandler->getParameter('authentication.direct_third_party')) {
            $username = !empty($email) ? $email : $id;
            $user = $this->loadUserByUsername($username);
            $oauthUser = new OauthUser();
            $oauthUser->setUser($user);
            $oauthUser->setService($service);
            $oauthUser->setOauthId($id);
            $this->em->persist($oauthUser);
            $this->em->flush();

            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Unable to find an active user identified by "%s".', $id)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $resourceOwner = $response->getResourceOwner();
        try {
            $user = $this->loadUserByServiceAndId(
                $resourceOwner->getName(),
                $response->getUsername(),
                $response->getEmail()
            );
            $this->saveResourceOwnerToken($resourceOwner, $response->getAccessToken());

            return $user;
        } catch (\Exception $e) {
            $name = $response->getRealName();
            $nameArray = explode(' ', $name, 2);
            $firstName = $response->getFirstName();
            $lastName = $response->getLastName();
            if (empty($firstName) || empty($lastName)) {
                if (array_key_exists(0, $nameArray)) {
                    $firstName = ucfirst(strtolower($nameArray[0]));
                }
                if (array_key_exists(1, $nameArray)) {
                    $lastName = ucfirst(strtolower($nameArray[1]));
                }
            }

            $user = [];
            $user['firstName'] = $firstName;
            $user['lastName'] = $lastName;
            $user['username'] = $this->createUsername($response->getNickname());
            $user['email'] = $response->getEmail();

            // Check if an account with the same email already exists
            try {
                $this->loadUserByUsername($user['email']);
                $user['platformMail'] = $user['email'];
            } catch (UsernameNotFoundException $e) {
                $user['platformMail'] = null;
            }

            $this->session->set('claroline.oauth.user', $user);

            $resourceOwnerArray = [
                'name' => $resourceOwner->getName(),
                'id' => $response->getUsername(),
            ];
            $this->session->set('claroline.oauth.resource_owner', $resourceOwnerArray);
            $this->session->set('claroline.oauth.resource_owner_token', $resourceOwner);
            $this->saveResourceOwnerToken($resourceOwner, $response->getAccessToken());

            throw $e;
        }
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
        return $this->getEntityName() === $class || is_subclass_of($class, $this->getEntityName());
    }

    private function createUsername($username)
    {
        $username = preg_replace('/\s/', '.', strtolower(trim($username)));
        $user = $this->em->getRepository('ClarolineCoreBundle:User')->findByName($username);

        if (0 === count($user)) {
            return $username;
        } else {
            return $username.count($user);
        }
    }

    private function saveResourceOwnerToken($resourceOwner, $token)
    {
        $tokenInfo = [
            'resourceOwnerName' => $resourceOwner->getName(),
            'token' => $token,
        ];

        $this->session->set('claroline.oauth.resource_owner_token', $tokenInfo);
    }
}

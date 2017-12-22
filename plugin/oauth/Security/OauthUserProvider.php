<?php

namespace Icap\OAuthBundle\Security;

use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Icap\OAuthBundle\Entity\OauthUser;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @DI\Service("icap.oauth.user_provider")
 */
class OauthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    private $em;
    /** @var Session */
    private $session;
    /** @var PlatformConfigurationHandler */
    private $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *   "em"                       = @DI\Inject("doctrine.orm.entity_manager"),
     *   "session"                  = @DI\Inject("session"),
     *   "platformConfigHandler"    = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param $em
     * @param Session                      $session
     * @param PlatformConfigurationHandler $platformConfigHandler
     */
    public function __construct(
        $em,
        Session $session,
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

    public function loadUserByServiceAndId($service, $id, $mail = null)
    {
        $oauthUser = $this->em->getRepository('IcapOAuthBundle:OauthUser')->findOneBy(
            ['service' => $service, 'oauthId' => $id]
        );

        if (!empty($oauthUser)) {
            return $oauthUser->getUser();
        }

        if ($this->platformConfigHandler->getParameter('direct_third_party_authentication')) {
            $username = !empty($mail) ? $mail : $id;
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
            $user['mail'] = $response->getEmail();
            // Check if an account with the same mail already exists
            try {
                $this->loadUserByUsername($user['mail']);
                $user['platformMail'] = $user['mail'];
            } catch (UsernameNotFoundException $e) {
                $user['platformMail'] = null;
            }

            $this->session->set('icap.oauth.user', $user);

            $resourceOwnerArray = [
                'name' => $resourceOwner->getName(),
                'id' => $response->getUsername(),
            ];
            $this->session->set('icap.oauth.resource_owner', $resourceOwnerArray);
            $this->session->set('icap.oauth.resource_owner_token', $resourceOwner);
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

        if (count($user) === 0) {
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

        $this->session->set('icap.oauth.resource_owner_token', $tokenInfo);
    }
}

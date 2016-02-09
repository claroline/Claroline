<?php

namespace Icap\OAuthBundle\Security;

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Claroline\CoreBundle\Entity\User;

/**
 * @DI\Service("icap.oauth.user_provider")
 */
class OauthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{

    private $em;
    private $utilities;
    /**
     * @var Session
     */
    private $session;

    /**
     * @DI\InjectParams({
     *   "em"           = @DI\Inject("doctrine.orm.entity_manager"),
     *   "session"      = @DI\Inject("session"),
     *   "utilities"    = @DI\Inject("claroline.utilities.misc")
     * })
     * @param $em
     * @param Session $session
     * @param $utilities
     */
    public function __construct(
        $em,
        Session $session,
        $utilities
    )
    {
        $this->em = $em;
        $this->session = $session;
        $this->utilities = $utilities;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->em->getRepository('ClarolineCoreBundle:User')->loadUserByUsername($username);
    }

    public function loadUserByServiceAndId($service, $id)
    {
        $oauthUser = $this->em->getRepository('IcapOAuthBundle:OauthUser')->findOneBy(
            array('service' => $service, 'oauthId' => $id)
        );

        if ($oauthUser === null) {
            throw new UsernameNotFoundException();
        }
        return $oauthUser->getUser();
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $content = $response->getResponse();
        $resourceOwner = $response->getResourceOwner();
        try {
            $user = $this->loadUserByServiceAndId($resourceOwner->getName(), $content['id']);

            return $user;
        } catch (\Exception $e) {
            $name = $response->getRealName();
            $nameArray = explode(" ", $name, 2);
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
            $user = array();
            $user['firstName'] = $firstName;
            $user['lastName'] = $lastName;
            $user['username'] = $this->createUsername($response->getNickname());
            $user['mail'] = $response->getEmail();

            $this->session->set('icap.oauth.user', $user);
            $resourceOwnerArray = array(
                'name'  => $resourceOwner->getName(),
                'id'    => $content['id']
            );
            $this->session->set('icap.oauth.resource_owner', $resourceOwnerArray);

            throw $e;
        }

    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritDoc}
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
            return ($username);
        } else {
            return $username . count($user);
        }
    }
}

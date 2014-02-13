<?php

namespace Claroline\CoreBundle\Library\Security;

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\Service;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Claroline\CoreBundle\Entity\User;

/**
 * @Service("claroline.facebook_provider")
 */
class FacebookProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    
    private $em;
    private $userManager;
    private $utilities;

    /**
     * @InjectParams({
     *   "em"          = @Inject("doctrine.orm.entity_manager"),
     *   "userManager" = @Inject("claroline.manager.user_manager"),
     *   "utilities"   = @Inject("claroline.utilities.misc")
     * })
     */ 
    public function __construct(
        $em,
        $userManager,
        $utilities
    )
    {
        $this->em = $em;
        $this->userManager = $userManager;
        $this->utilities = $utilities;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        $dql = 'SELECT u FROM Claroline\CoreBundle\Entity\User u
	    WHERE u.username LIKE :username
	    OR u.mail LIKE :username';
        $query = $this->em->createQuery($dql);
        $query->setParameter('username', $username);

        try {
            $user = $query->getSingleResult();
        } catch (NoResultException $e) {
            throw new UsernameNotFoundException(
                sprintf('Unable to find an active user identified by "%s".', $username)
            );
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        try {
            $user = $this->loadUserByUsername($response->getEmail());
        } catch (\Exception $e) {
            $user = new User();
            $content = $response->getResponse();
            $user->setFirstName($content['first_name']);
            $user->setLastName($content['last_name']);
            $user->setUsername($this->createUsername($response->getNickname()));
            $user->setPlainPassword($this->utilities->generateGuid());
            $user->setMail($response->getEmail());
            $user = $this->userManager->createUser($user);
        }

        return $user;
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
        $user = $this->em->getRepository('ClarolineCoreBundle:User')->findByName($username);

        if (count($user) === 0) {
            return ($username);
        } else {
            return $username . '#' . count($user);
        }
    }
}

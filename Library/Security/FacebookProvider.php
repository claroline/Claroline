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

    /**
     * @InjectParams({
     *   "em"          = @Inject("doctrine.orm.entity_manager"),
     *   "userManager" = @Inject("claroline.manager.user_manager")
     * })
     */ 
    public function __construct($em, $userManager)
    {
        $this->em = $em;
        $this->userManager = $userManager;
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
            $user->setFirstName($response->getRealname());
            $user->setLastName($response->getNickname());
            $user->setUsername($response->getUsername());
            $user->setPlainPassword('trololol');
            $user->setMail($response->getEmail());
            $this->userManager->createUser($user);
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
}

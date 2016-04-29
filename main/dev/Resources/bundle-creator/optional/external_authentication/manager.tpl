<?php

namespace [[Vendor]]\[[Bundle]]Bundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("[[vendor]].manager.security_manager")
 */
class SecurityManager
{
    private $sc;
    private $om;

    /**
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage"),
     *     "om"           = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage, $om)
    {
        $this->tokenStorage = $tokenStorage;
        $this->om = $om;
    }

    //not sure it'll work but it's a start ~you should check if the password is used properly
    public function authenticate($username, $password)
    {
        $userRepo = $this->get('doctrine.orm.entity_manager')->getRepository('ClarolineCoreBundle:User');
        $userLoaded = $userRepo->loadUserByUsername($user->getUsername());
        $token = new UsernamePasswordToken($userLoaded, $password, 'main', $userLoaded->getRoles());
        $this->tokenStorage->setToken($token);
    }
}

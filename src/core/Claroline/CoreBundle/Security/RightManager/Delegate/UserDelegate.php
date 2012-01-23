<?php

namespace Claroline\CoreBundle\Security\RightManager\Delegate;

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Exception\SecurityException;

class UserDelegate implements SubjectDelegateInterface
{
    /** @var EntityManager */
    private $em;
    
    /** @var UserRepository */
    private $userRepository;    
       
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->userRepository = $this->em->getRepository('Claroline\CoreBundle\Entity\User');
    }
    
    public function buildSecurityIdentity($subject)
    {
        if ($subject->getId() == 0)
        {
            throw new SecurityException(
                "The user must be saved before being granted any right.",
                SecurityException::INVALID_USER_STATE
            );
        }
        
        return UserSecurityIdentity::fromAccount($subject);
    }
    
    public function buildSubject($sid)
    {
        $userName = $sid->getUsername();
        $user = $this->userRepository->findOneByUsername($userName);
        
        return $user;
    }
}
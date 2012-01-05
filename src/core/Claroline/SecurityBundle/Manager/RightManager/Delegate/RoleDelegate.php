<?php

namespace Claroline\SecurityBundle\Manager\RightManager\Delegate;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Claroline\SecurityBundle\Exception\RightManagerException;

class RoleDelegate implements SubjectDelegateInterface
{
    /** @var EntityManager */
    private $em;
    
    /** @var EntityRepository */
    private $roleRepository;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->roleRepository = $this->em->getRepository('Claroline\SecurityBundle\Entity\Role');
    }
    
    public function buildSecurityIdentity($subject)
    {
        if ($subject->getId() === null)
        {
            throw new RightManagerException(
                "The role must be saved before being granted any right.",
                RightManagerException::INVALID_ROLE_STATE
            );
        }
        
        return new RoleSecurityIdentity($subject->getName());
    }
    
    public function buildSubject($sid)
    {
        $roleName = $sid->getRole();
        $role = $this->roleRepository->findOneByName($roleName);
        
        return $role;
    }
}
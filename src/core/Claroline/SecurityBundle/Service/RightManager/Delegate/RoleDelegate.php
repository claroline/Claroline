<?php
namespace Claroline\SecurityBundle\Service\RightManager\Delegate;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Claroline\SecurityBundle\Service\Exception\RightManagerException;

class RoleDelegate implements SubjectDelegateInterface
{
    /** @var EntityManager */
    private $em;
    
    /** @var EntityRepository */
    private $roleRepository;
    
    function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->roleRepository = $this->em->getRepository('Claroline\SecurityBundle\Entity\Role');
    }
    
    public function buildSecurityIdentity($subject)
    {
        if($subject->getId() == 0)
        {
            throw new RightManagerException(
                "The role must be saved before being granted any right.",
                RightManagerException :: INVALID_ROLE_STATE
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

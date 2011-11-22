<?php
namespace Claroline\SecurityBundle\Service\RightManager\Delegate;

use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

 class EntityDelegate implements TargetDelegateInterface
{
    public function getAces(Acl $acl)
    {
        return $acl->getObjectAces();
    }
    
    public function insertAce(Acl $acl, SecurityIdentityInterface $sid, $mask)
    {
        $acl->insertObjectAce($sid, $mask);
    }

    
    public function deleteAce(Acl $acl, $aceIndex)
    {
        $acl->deleteObjectAce($aceIndex);
    }
    
    
    public function buildObjectIdentity($target)
    {
        return ObjectIdentity::fromDomainObject($target);
    }
    
    
    public function updateAce(Acl $acl, $aceIndex, $mask)
    {
        $acl->updateObjectAce($aceIndex, $mask);
    }
}


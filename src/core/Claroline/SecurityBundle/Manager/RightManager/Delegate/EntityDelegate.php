<?php

namespace Claroline\SecurityBundle\Manager\RightManager\Delegate;

use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\InvalidDomainObjectException;
use Claroline\SecurityBundle\Exception\RightManagerException;

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
        try
        {
            return ObjectIdentity::fromDomainObject($target);
        }
        catch (InvalidDomainObjectException $ex)
        {
            throw new RightManagerException(
                "The entity must be saved before any right is granted on it (and it must have " 
                . "a valid identifier accessible via a getId or a getObjectIdentifier method).",
                RightManagerException::INVALID_ENTITY_STATE
            );
        }
    }
    
    public function updateAce(Acl $acl, $aceIndex, $mask)
    {
        $acl->updateObjectAce($aceIndex, $mask);
    }
}
<?php

namespace Claroline\CoreBundle\Library\Security\RightManager\Delegate;

use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;
use Claroline\CoreBundle\Library\Security\Acl\ClassIdentity;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.security.right_manager.delegate.class")
 */
class ClassDelegate implements TargetDelegateInterface
{
    public function getAces(Acl $acl)
    {
        return $acl->getClassAces();
    }

    public function insertAce(Acl $acl, SecurityIdentityInterface $sid, $mask)
    {
        $acl->insertClassAce($sid, $mask);
    }

    public function deleteAce(Acl $acl, $aceIndex)
    {
        $acl->deleteClassAce($aceIndex);
    }

    public function buildObjectIdentity($target)
    {
        return ClassIdentity::fromDomainClass($target);
    }

    public function updateAce(Acl $acl, $aceIndex, $mask)
    {
        $acl->updateClassAce($aceIndex, $mask);
    }
}
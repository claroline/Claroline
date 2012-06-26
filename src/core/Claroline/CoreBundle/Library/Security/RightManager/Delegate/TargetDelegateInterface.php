<?php

namespace Claroline\CoreBundle\Library\Security\RightManager\Delegate;

use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

interface TargetDelegateInterface
{
    function getAces(Acl $acl);
    function insertAce(Acl $acl, SecurityIdentityInterface $sid, $mask);
    function deleteAce(Acl $acl, $aceIndex);
    function updateAce(Acl $acl, $aceIndex, $mask);
    function buildObjectIdentity($target);
}
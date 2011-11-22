<?php
namespace Claroline\SecurityBundle\Service\RightManager\Delegate;

use Symfony\Component\Security\Acl\Domain\Acl;
use Symfony\Component\Security\Acl\Model\SecurityIdentityInterface;

interface SubjectDelegateInterface
{
    function buildSecurityIdentity($subject);
    function buildSubject($sid);
}


<?php

namespace Claroline\CoreBundle\Security\RightManager\Delegate;

interface SubjectDelegateInterface
{
    function buildSecurityIdentity($subject);
    function buildSubject($sid);
}
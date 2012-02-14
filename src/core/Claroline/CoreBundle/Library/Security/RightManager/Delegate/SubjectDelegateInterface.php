<?php

namespace Claroline\CoreBundle\Library\Security\RightManager\Delegate;

interface SubjectDelegateInterface
{
    function buildSecurityIdentity($subject);
    function buildSubject($sid);
}
<?php

namespace Claroline\CoreBundle\Manager\RightManager\Delegate;

interface SubjectDelegateInterface
{
    function buildSecurityIdentity($subject);
    function buildSubject($sid);
}
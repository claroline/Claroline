<?php

namespace Claroline\SecurityBundle\Manager\RightManager\Delegate;

interface SubjectDelegateInterface
{
    function buildSecurityIdentity($subject);
    function buildSubject($sid);
}
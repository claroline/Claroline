<?php

namespace Claroline\CoreBundle\Library\Security\RightManager;

interface RightManagerInterface
{
    function addRight($target, $subject, $rightMask);
    function removeRight($target, $subject, $rightMask);
    function removeAllRights($target, $subject);
    function setRight($target, $subject, $rightMask);
    function hasRight($target, $subject, $rightMask);
    function getUsersWithRight($target, $rightMask);
}
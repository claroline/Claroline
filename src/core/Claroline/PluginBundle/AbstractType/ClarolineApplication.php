<?php

namespace Claroline\PluginBundle\AbstractType;

abstract class ClarolineApplication extends ClarolinePlugin
{
    abstract public function getIndexRoute();

    public function isEligibleForPlatformIndex()
    {
        return false;
    }

    public function getDefaultAccessRoles()
    {
        return array();
    }
}
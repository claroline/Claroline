<?php

namespace Claroline\PluginBundle\AbstractType;

abstract class ClarolineApplication extends ClarolinePlugin
{
    abstract public function getLaunchers();

    public function isEligibleForPlatformIndex()
    {
        return false;
    }
}
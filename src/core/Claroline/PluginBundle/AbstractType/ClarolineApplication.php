<?php

namespace Claroline\PluginBundle\AbstractType;

abstract class ClarolineApplication extends ClarolinePlugin
{
    /**
     * This method must return an instance or an array of instances 
     * of Claroline\PluginBundle\Widget\ApplicationLauncher
     */
    abstract public function getLaunchers();

    public function isEligibleForPlatformIndex()
    {
        return false;
    }
}
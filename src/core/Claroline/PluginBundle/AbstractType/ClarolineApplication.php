<?php

namespace Claroline\PluginBundle\AbstractType;

abstract class ClarolineApplication extends ClarolinePlugin
{
    // Must return an array of Claroline\GUIBundle\Widget\ApplicationLauncher instances
    abstract public function getLaunchers();

    public function isEligibleForPlatformIndex()
    {
        return false;
    }
}
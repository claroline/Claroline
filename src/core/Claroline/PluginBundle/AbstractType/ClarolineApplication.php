<?php

namespace Claroline\PluginBundle\AbstractType;

use Claroline\CommonBundle\Exception\ClarolineException;

abstract class ClarolineApplication extends ClarolinePlugin
{
    /**
     * This method must return an array of instances (at least one)
     * of Claroline\PluginBundle\Widget\ApplicationLauncher
     */
    abstract public function getLaunchers();

    public function isEligibleForPlatformIndex()
    {
        $indexRoute = $this->getPlatformIndexRoute();
        
        if (! is_string($indexRoute) || empty($indexRoute))
        {
            // the method getPlatformIndexRoute() hasn't been 
            // overriden, or in a incorrect manner.
            return false;
        }
        
        return true;
    }
    
    public function getPlatformIndexRoute()
    {
        return null;
    }
}
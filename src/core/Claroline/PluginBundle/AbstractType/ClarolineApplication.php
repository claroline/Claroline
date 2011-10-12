<?php

namespace Claroline\PluginBundle\AbstractType;

use Claroline\CommonBundle\Exception\ClarolineException;

abstract class ClarolineApplication extends ClarolinePlugin
{
    /**
     * This method must return an array of instances (at least one)
     * of Claroline\PluginBundle\Widget\ApplicationLauncher
     * 
     * @return array
     */
    abstract public function getLaunchers();

    /**
     * This method must return the string identifier of the index route
     * of the application.
     * 
     * @return string
     */
    abstract public function getIndexRoute();
    
    /**
     * If this method is overriden and returns true, the platform could be configured 
     * to let the application run when the site index is requested.
     *
     * @return boolean
     */
    public function isEligibleForPlatformIndex()
    {
        return false;
    }
}
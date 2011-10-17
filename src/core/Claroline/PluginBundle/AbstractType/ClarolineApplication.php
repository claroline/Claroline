<?php

namespace Claroline\PluginBundle\AbstractType;

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
    
    /**
     * If this method is overriden and returns true, the platform could be configured 
     * to make the application the target of the connection button/link. It only makes
     * sense if the application index route is reserved to authenticated users.
     *
     * @return boolean
     */
    public function isEligibleForConnectionTarget()
    {
        return false;
    }
}
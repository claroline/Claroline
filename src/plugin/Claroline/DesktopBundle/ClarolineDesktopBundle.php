<?php

namespace Claroline\DesktopBundle;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\Widget\ApplicationLauncher;

class ClarolineDesktopBundle extends ClarolineApplication
{
    public function getLaunchers()
    {
        $launchers = array(
            new ApplicationLauncher(
                'claroline_desktop_index', 
                'desktop.application_name', 
                array('ROLE_USER')
            )
        );
        
        return $launchers;
    }
    
    public function getRoutingPrefix()
    {
        return 'desktop';
    }
    
    public function getIndexRoute()
    {
        return 'claroline_desktop_index';
    }
    
    public function isEligibleForPlatformIndex()
    {
        return true;
    }
    
    public function isEligibleForConnectionTarget()
    {
        return true;
    }
}
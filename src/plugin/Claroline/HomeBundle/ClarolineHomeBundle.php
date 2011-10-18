<?php

namespace Claroline\HomeBundle;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\Widget\ApplicationLauncher;

class ClarolineHomeBundle extends ClarolineApplication
{
    public function getLaunchers()
    {
        $launchers = array(
            new ApplicationLauncher(
                'claroline_home_index', 
                'home.application_name', 
                array('ROLE_ANONYMOUS')
            )
        );
        
        return $launchers;
    }
    
    public function getRoutingPrefix()
    {
        return 'home';
    }
    
    public function getIndexRoute()
    {
        return 'claroline_home_index';
    }
    
    public function isEligibleForPlatformIndex()
    {
        return true;
    }
}
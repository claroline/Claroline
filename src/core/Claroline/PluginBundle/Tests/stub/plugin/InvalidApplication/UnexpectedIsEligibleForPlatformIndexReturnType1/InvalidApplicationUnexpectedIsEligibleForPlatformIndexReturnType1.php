<?php

namespace InvalidApplication\UnexpectedIsEligibleForPlatformIndexReturnType1;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\Widget\ApplicationLauncher;

class InvalidApplicationUnexpectedIsEligibleForPlatformIndexReturnType1 extends ClarolineApplication
{
    public function getLaunchers()
    {
        return array(
            new ApplicationLauncher('route_test', 'translation_test', array('ROLE_TEST'))
        );
    }
    
    public function getIndexRoute()
    {
        return 'route_test';
    }
    
    /**
     * Must return a boolean.
     */
    public function isEligibleForPlatformIndex()
    {
        return null;
    }
}
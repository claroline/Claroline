<?php

namespace Valid\EligibleForConnectionTarget1;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\Widget\ApplicationLauncher;

class ValidEligibleForConnectionTarget1 extends ClarolineApplication
{
    public function getLaunchers()
    {
        return array(
            new ApplicationLauncher('route_test', 'translation_test', array('ROLE_USER'))
        );
    }
    
    public function getIndexRoute()
    {
        return 'valid_eligible_target_1';
    }
    
    public function isEligibleForConnectionTarget()
    {
        return true;
    }
}
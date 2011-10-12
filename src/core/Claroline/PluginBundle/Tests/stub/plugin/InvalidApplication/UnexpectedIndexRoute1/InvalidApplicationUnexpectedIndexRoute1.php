<?php

namespace InvalidApplication\UnexpectedIndexRoute1;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\Widget\ApplicationLauncher;

class InvalidApplicationUnexpectedIndexRoute1 extends ClarolineApplication
{
    public function getLaunchers()
    {
        return array(
            new ApplicationLauncher('route_test', 'translation_test', array('ROLE_TEST'))
        );
    }
    
    /**
     * Must return a string.
     */
    public function getIndexRoute()
    {
        return null;
    }
}
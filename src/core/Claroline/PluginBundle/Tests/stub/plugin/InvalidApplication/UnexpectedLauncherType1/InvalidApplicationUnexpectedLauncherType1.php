<?php

namespace InvalidApplication\UnexpectedLauncherType1;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;

class InvalidApplicationUnexpectedLauncherType1 extends ClarolineApplication
{
    public function getLaunchers()
    {
        return array('not_a_launcher_instance');
    }
    
    public function getIndexRoute()
    {
        return 'bad_launcher_type_index';
    }
}
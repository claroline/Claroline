<?php

namespace InvalidApplication\UnexpectedLauncherType_1;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;

class InvalidApplicationUnexpectedLauncherType_1 extends ClarolineApplication
{
    public function getLaunchers()
    {
        return array('not_a_launcher_instance');
    }
}
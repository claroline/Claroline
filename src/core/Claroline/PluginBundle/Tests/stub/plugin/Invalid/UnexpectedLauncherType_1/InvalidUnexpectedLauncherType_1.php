<?php

namespace Invalid\UnexpectedLauncherType_1;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;

class InvalidUnexpectedLauncherType_1 extends ClarolineApplication
{
    public function getLaunchers()
    {
        return array('not_a_launcher_instance');
    }
}
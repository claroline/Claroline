<?php

namespace InvalidApplication\UnexpectedGetLauncherReturnValue1;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\Widget\ApplicationLauncher;

class InvalidApplicationUnexpectedGetLauncherReturnValue1 extends ClarolineApplication
{
    /**
     * Invalid because this method must return an array.
     */
    public function getLaunchers()
    {
        return new ApplicationLauncher('route_test', 'translation_test', array('ROLE_TEST'));
    }
}
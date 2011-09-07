<?php

namespace ValidApplication\TwoLaunchers;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\PluginBundle\Widget\ApplicationLauncher;

class ValidApplicationTwoLaunchers extends ClarolineApplication
{
    public function getLaunchers()
    {
        $launcher_1 = new ApplicationLauncher(
                'route_id_1',
                'trans_key_1',
                array("ROLE_TEST_1", "ROLE_TEST_2"));
        
        $launcher_2 = new ApplicationLauncher(
                'route_id_2',
                'trans_key_2',
                array('ROLE_TEST_1'));

        return array($launcher_1, $launcher_2);
    }
}
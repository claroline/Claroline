<?php

namespace ValidApplication\TwoLaunchers;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;
use Claroline\GUIBundle\Widget\ApplicationLauncher;

class ValidApplicationTwoLaunchers extends ClarolineApplication
{
    public function getLaunchers()
    {
        $firstLauncher = new ApplicationLauncher(
            'route_id_1',
            'trans_key_1',
            array("ROLE_TEST_1", "ROLE_TEST_2")
        );
        
        $secondLauncher = new ApplicationLauncher(
            'route_id_2',
            'trans_key_2',
            array('ROLE_TEST_1')
        );

        return array($firstLauncher, $secondLauncher);
    }
}
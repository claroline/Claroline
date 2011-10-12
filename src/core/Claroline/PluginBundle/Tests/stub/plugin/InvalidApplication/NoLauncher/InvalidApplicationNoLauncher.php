<?php

namespace InvalidApplication\NoLauncher;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;

class InvalidApplicationNoLauncher extends ClarolineApplication
{
    public function getLaunchers()
    {
        return array();
    }
    
    public function getIndexRoute()
    {
        return 'no_launcher_index';
    }
}
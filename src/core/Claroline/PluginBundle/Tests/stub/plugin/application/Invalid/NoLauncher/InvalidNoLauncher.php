<?php

namespace Invalid\NoLauncher;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;

class InvalidNoLauncher extends ClarolineApplication
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
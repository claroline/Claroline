<?php

namespace Invalid\NoLauncher;

use Claroline\PluginBundle\AbstractType\ClarolineApplication;

class InvalidNoLauncher extends ClarolineApplication
{
    public function getLaunchers()
    {
        return array();
    }
}
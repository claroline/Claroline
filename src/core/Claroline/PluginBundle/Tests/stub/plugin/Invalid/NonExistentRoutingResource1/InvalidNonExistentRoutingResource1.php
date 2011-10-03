<?php

namespace Invalid\NonExistentRoutingResource1;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidNonExistentRoutingResource1 extends ClarolinePlugin
{
    public function getRoutingResourcesPaths()
    {
        return 'wrong/path/file.yml';
    }
}
<?php

namespace Invalid\NonExistentRoutingResource_1;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidNonExistentRoutingResource_1 extends ClarolinePlugin
{
    public function getRoutingResourcesPaths()
    {
        return 'wrong/path/file.yml';
    }
}
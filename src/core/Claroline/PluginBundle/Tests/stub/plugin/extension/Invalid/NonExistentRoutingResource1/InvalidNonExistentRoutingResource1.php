<?php

namespace Invalid\NonExistentRoutingResource1;

use Claroline\PluginBundle\AbstractType\ClarolineExtension;

class InvalidNonExistentRoutingResource1 extends ClarolineExtension
{
    public function getRoutingResourcesPaths()
    {
        return 'wrong/path/file.yml';
    }
}
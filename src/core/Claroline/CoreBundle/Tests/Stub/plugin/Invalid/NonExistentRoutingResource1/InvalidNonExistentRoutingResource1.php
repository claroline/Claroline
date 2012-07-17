<?php

namespace Invalid\NonExistentRoutingResource1;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidNonExistentRoutingResource1 extends PluginBundle
{
    public function getRoutingResourcesPaths()
    {
        return 'wrong/path/file.yml';
    }
}
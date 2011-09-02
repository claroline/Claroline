<?php

namespace Invalid\UnloadableRoutingResource_1;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidUnloadableRoutingResource_1 extends ClarolinePlugin
{
    public function getRoutingResourcesPaths()
    {
        $unloadableYamlPath = __DIR__
            . DIRECTORY_SEPARATOR
            . 'Resources'
            . DIRECTORY_SEPARATOR
            . 'config'
            . DIRECTORY_SEPARATOR
            . 'routing.yml';

        return $unloadableYamlPath;
    }
}
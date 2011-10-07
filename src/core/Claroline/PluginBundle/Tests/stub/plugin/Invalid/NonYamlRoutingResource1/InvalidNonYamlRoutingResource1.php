<?php

namespace Invalid\NonYamlRoutingResource1;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidNonYamlRoutingResource1 extends ClarolinePlugin
{
    public function getRoutingResourcesPaths()
    {
        $nonYamlPath = __DIR__
            . DIRECTORY_SEPARATOR
            . 'Resources'
            . DIRECTORY_SEPARATOR
            . 'config'
            . DIRECTORY_SEPARATOR
            . 'routing.foo';

        return $nonYamlPath;
    }
}
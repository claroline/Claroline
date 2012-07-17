<?php

namespace Invalid\NonYamlRoutingResource1;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidNonYamlRoutingResource1 extends PluginBundle
{
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $nonYamlPath = __DIR__ ."{$ds}Resources{$ds}config{$ds}routing.foo";

        return $nonYamlPath;
    }
}
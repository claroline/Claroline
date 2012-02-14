<?php

namespace Invalid\UnloadableRoutingResource1;

use Claroline\CoreBundle\Library\Plugin\ClarolineExtension;

class InvalidUnloadableRoutingResource1 extends ClarolineExtension
{
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $unloadableYamlPath = __DIR__ . "{$ds}Resources{$ds}config{$ds}routing.yml";

        return $unloadableYamlPath;
    }
}
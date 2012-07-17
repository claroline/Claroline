<?php

namespace Invalid\UnloadableResourceFile1;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidUnloadableResourceFile1 extends PluginBundle
{
    public function getCustomResourcesFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $unloadableYamlPath = __DIR__ . "{$ds}Resources{$ds}config{$ds}resources.yml";

        return $unloadableYamlPath;
    }
}
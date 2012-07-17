<?php

namespace Invalid\NonYamlResourceFile1;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidNonYamlResourceFile1 extends PluginBundle
{
    public function getCustomResourcesFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $nonYamlPath = __DIR__ ."{$ds}Resources{$ds}config{$ds}resources.foo";

        return $nonYamlPath;
    }
}
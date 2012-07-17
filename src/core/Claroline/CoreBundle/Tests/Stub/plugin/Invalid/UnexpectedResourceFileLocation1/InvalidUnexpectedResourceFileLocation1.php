<?php

namespace Invalid\UnexpectedResourceFileLocation1;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidUnexpectedResourceFileLocation1 extends PluginBundle
{
    public function getCustomResourcesFile()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = __DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}Misc{$ds}misplaced_resource_file.yml";

        return $path;
    }
}
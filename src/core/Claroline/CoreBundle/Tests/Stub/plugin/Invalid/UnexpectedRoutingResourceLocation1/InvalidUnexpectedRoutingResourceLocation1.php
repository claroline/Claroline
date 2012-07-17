<?php

namespace Invalid\UnexpectedRoutingResourceLocation1;

use Claroline\CoreBundle\Library\PluginBundle;

class InvalidUnexpectedRoutingResourceLocation1 extends PluginBundle
{
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = __DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}Misc{$ds}misplaced_routing_file.yml";

        return $path;
    }
}
<?php

namespace Invalid\UnexpectedRoutingResourceLocation1;

use Claroline\CoreBundle\AbstractType\ClarolineExtension;

class InvalidUnexpectedRoutingResourceLocation1 extends ClarolineExtension
{
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $path = __DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}..{$ds}Misc{$ds}misplaced_routing_file.yml";

        return $path;
    }
}
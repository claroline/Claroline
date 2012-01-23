<?php

namespace Invalid\NonYamlRoutingResource1;

use Claroline\CoreBundle\Plugin\ClarolineExtension;

class InvalidNonYamlRoutingResource1 extends ClarolineExtension
{
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $nonYamlPath = __DIR__ ."{$ds}Resources{$ds}config{$ds}routing.foo";

        return $nonYamlPath;
    }
}
<?php

namespace Invalid\UnexpectedRoutingResourceLocation_1;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidUnexpectedRoutingResourceLocation_1 extends ClarolinePlugin
{
    public function getRoutingResourcesPaths()
    {
        $path = __DIR__
            . DIRECTORY_SEPARATOR
            . '..'
            . DIRECTORY_SEPARATOR
            . '..'
            . DIRECTORY_SEPARATOR
            . '..'
            . DIRECTORY_SEPARATOR
            . 'misc'
            . DIRECTORY_SEPARATOR
            . 'misplaced_routing_file.yml';

        return $path;
    }
}
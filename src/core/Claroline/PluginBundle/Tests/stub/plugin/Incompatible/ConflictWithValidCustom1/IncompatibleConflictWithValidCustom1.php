<?php

namespace Incompatible\ConflictWithValidCustom1;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

/**
 * Incompatible with the Valid\Custom\ValidCustom stub because 
 * the routing prefixes are the same.
 */
class IncompatibleConflictWithValidCustom1 extends ClarolinePlugin
{
    public function getRoutingPrefix()
    {
        return 'custom_routing_prefix';
    }
}
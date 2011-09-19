<?php

namespace Incompatible\ConflictWithValidCustom_1;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

/**
 * Incompatible with the Valid\Custom\ValidCustom stub because 
 * the routing prefixes are the same.
 */
class IncompatibleConflictWithValidCustom_1 extends ClarolinePlugin
{
    public function getPrefix()
    {
        return 'custom_routing_prefix';
    }
}
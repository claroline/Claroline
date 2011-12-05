<?php

namespace UnexpectedNamespace\UnloadableBundleClass2;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class InvalidUnloadableBundleClass2
{
    /*
     * Unloadable because the class vendor namespace doesn't
     * match the bundle vendor directory
     */
}
<?php

namespace VendorX\DummyPluginBundle;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class VendorXDummyPluginBundle extends ClarolinePlugin
{
    public function getRoutingResourcesPaths()
    {
        return array(
            __DIR__.'/Resources/config/special/routing1.yml',
            __DIR__.'/Resources/config/special/routing2.yml'
        );
    }
}
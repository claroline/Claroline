<?php

namespace VendorX\DummyPluginBundle;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

class VendorXDummyPluginBundle extends ClarolinePlugin
{
    public function getPrefix()
    {
        return 'vendor_x_dummy';
    }
    
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        return array(
            __DIR__.$ds.'Resources'.$ds.'config'.$ds.'special'.$ds.'routing1.yml',
            __DIR__.$ds.'Resources'.$ds.'config'.$ds.'special'.$ds.'routing2.yml'
        );
    }
}
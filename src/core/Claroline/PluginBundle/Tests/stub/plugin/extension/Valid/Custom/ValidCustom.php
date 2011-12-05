<?php

namespace Valid\Custom;

use Claroline\PluginBundle\AbstractType\ClarolineExtension;

/**
 * Plugin overriding all the ClarolinePlugin methods.
 */
class ValidCustom extends ClarolineExtension
{
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $commonPath = $this->getPath().$ds.'Resources'.$ds.'config';
        $firstPath = $commonPath.$ds.'routing'.$ds.'routing.yml';
        $secondPath = $commonPath.$ds.'special_routing'.$ds.'routing.yml';
        
        return array($firstPath, $secondPath);
    }

    public function getRoutingPrefix()
    {
        return 'custom_routing_prefix';
    }
    
    public function getNameTranslationKey()
    {
        return 'Custom name translation key';
    }

    public function getDescriptionTranslationKey()
    {
        return 'Custom description translation key';
    }
}
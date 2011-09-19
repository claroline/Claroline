<?php

namespace Valid\Custom;

use Claroline\PluginBundle\AbstractType\ClarolinePlugin;

/**
 * Plugin overriding all the ClarolinePlugin methods.
 */
class ValidCustom extends ClarolinePlugin
{
    public function getRoutingResourcesPaths()
    {
        $ds = DIRECTORY_SEPARATOR;
        $commonPath = $this->getPath().$ds.'Resources'.$ds.'config';
        $firstPath = $commonPath.$ds.'routing'.$ds.'routing.yml';
        $secondPath = $commonPath.$ds.'special_routing'.$ds.'routing.yml';
        
        return array($firstPath, $secondPath);
    }

    public function getPrefix()
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
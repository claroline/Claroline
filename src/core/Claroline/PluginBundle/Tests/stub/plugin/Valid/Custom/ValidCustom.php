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
        $commonPath = $this->getPath()
            . DIRECTORY_SEPARATOR
            . 'Resources'
            . DIRECTORY_SEPARATOR
            . 'config';
        $path_1 = $commonPath
            . DIRECTORY_SEPARATOR
            . 'routing'
            . DIRECTORY_SEPARATOR
            . 'routing.yml';
        $path_2 = $commonPath
            . DIRECTORY_SEPARATOR
            . 'special_routing'
            . DIRECTORY_SEPARATOR
            . 'routing.yml';
        
        return array($path_1, $path_2);
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
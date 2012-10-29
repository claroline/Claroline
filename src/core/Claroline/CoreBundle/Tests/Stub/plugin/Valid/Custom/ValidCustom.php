<?php

namespace Valid\Custom;

use Claroline\CoreBundle\Library\PluginBundle;

/**
 * Plugin overriding all the ClarolinePlugin methods.
 */
class ValidCustom extends PluginBundle
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

    public function getDescriptionTranslationKey()
    {
        return 'Custom description translation key';
    }
}
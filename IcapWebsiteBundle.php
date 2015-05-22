<?php

namespace Icap\WebsiteBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class IcapWebsiteBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'icap_website');
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $bundleClass = get_class($bundle);
        $config = new ConfigurationBuilder();
        $emptyConfigs = array(
            'Innova\AngularJSBundle\InnovaAngularJSBundle',
        );
        if (in_array($bundleClass, $emptyConfigs)) {
            return $config;
        }
        return false;
    }
}
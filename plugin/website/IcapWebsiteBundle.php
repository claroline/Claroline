<?php

namespace Icap\WebsiteBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Icap\WebsiteBundle\Installation\AdditionalInstaller;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IcapWebsiteBundle extends DistributionPluginBundle implements ConfigurationProviderInterface
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null, 'icap_website');
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $bundleClass = get_class($bundle);
        $config = new ConfigurationBuilder();
        $emptyConfigs = [
            'Innova\AngularJSBundle\InnovaAngularJSBundle',
        ];
        if (in_array($bundleClass, $emptyConfigs)) {
            return $config;
        }

        return false;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}

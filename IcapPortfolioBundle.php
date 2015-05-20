<?php

namespace Icap\PortfolioBundle;

use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\InstallationBundle\Bundle\InstallableBundle;
use Icap\PortfolioBundle\Installation\AdditionalInstaller;
use Claroline\CoreBundle\Library\PluginBundle;

class IcapPortfolioBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        if (file_exists($routingFile = $this->getPath() . '/Resources/config/routing.yml')) {
            $config->addRoutingResource($routingFile);
        }

        return $config;
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

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures/Required';
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}

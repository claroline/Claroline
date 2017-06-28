<?php

namespace Icap\PortfolioBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Icap\PortfolioBundle\Installation\AdditionalInstaller;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IcapPortfolioBundle extends DistributionPluginBundle implements ConfigurationProviderInterface
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        if (file_exists($routingFile = $this->getPath().'/Resources/config/routing.yml')) {
            $config->addRoutingResource($routingFile);
        }

        return $config;
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $bundleClass = get_class($bundle);
        $config = new ConfigurationBuilder();
        $emptyConfigs = [
            'Innova\AngularJSBundle\InnovaAngularJSBundle',
            'Innova\AngularUIResourcePickerBundle\InnovaAngularUIResourcePickerBundle',
            'Innova\AngularUITinyMCEBundle\InnovaAngularUITinyMCEBundle',
        ];
        if (in_array($bundleClass, $emptyConfigs)) {
            return $config;
        }

        return false;
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures/Required';
    }

    public function getRequiredPlugins()
    {
        return ['Claroline\\TeamBundle\\ClarolineTeamBundle'];
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}

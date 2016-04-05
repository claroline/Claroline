<?php

namespace Innova\PathBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Innova\PathBundle\Installation\AdditionalInstaller;

/**
 * Bundle class.
 */
class InnovaPathBundle extends PluginBundle implements AutoConfigurableInterface, ConfigurationProviderInterface
{
    public function supports($environment)
    {
        return true;
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__ . '/Resources/config/routing.yml', null, 'innova_path');
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $bundleClass = get_class($bundle);
        $config = new ConfigurationBuilder();

        $emptyConfigs = array(
            'Innova\AngularUIPageslideBundle\InnovaAngularUIPageslideBundle',
        );

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

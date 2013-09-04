<?php

namespace Claroline\InstallationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class ClarolineInstallationBundle extends Bundle implements AutoConfigurableInterface, ConfigurationProviderInterface
{
    public function supports($environment)
    {
        return true;
    }

    public function getConfiguration($environment)
    {
        return new ConfigurationBuilder();
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $config = new ConfigurationBuilder();
        $bundles = array(
            'Symfony\Bundle\FrameworkBundle\FrameworkBundle' => 'framework',
            'Doctrine\Bundle\DoctrineBundle\DoctrineBundle' => 'doctrine'
        );

        if (in_array($bundleClass = get_class($bundle), array_keys($bundles))) {
            if (in_array($environment, array('prod', 'dev', 'test'))) {
                return $config->addContainerResource(
                    __DIR__ . "/Resources/config/suggested/{$bundles[$bundleClass]}_{$environment}.yml"
                );
            }
        }

        if ($bundle instanceof \Symfony\Bundle\TwigBundle\TwigBundle) {
            return $config->addContainerResource(
                __DIR__ . "/Resources/config/suggested/twig.yml"
            );
        }
    }
}

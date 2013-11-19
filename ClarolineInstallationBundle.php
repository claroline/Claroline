<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

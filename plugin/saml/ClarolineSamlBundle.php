<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\SamlBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\SamlBundle\DependencyInjection\Compiler\SamlConfigPass;
use LightSaml\SpBundle\LightSamlSpBundle;
use LightSaml\SymfonyBridgeBundle\LightSamlSymfonyBridgeBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineSamlBundle extends DistributionPluginBundle implements ConfigurationProviderInterface
{
    public function isActiveByDefault()
    {
        return false;
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SamlConfigPass());
    }

    public function getRequiredThirdPartyBundles(string $environment): array
    {
        return [
            new LightSamlSymfonyBridgeBundle(),
            new LightSamlSpBundle(),
        ];
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $config = new ConfigurationBuilder();
        $bundleClass = get_class($bundle);
        $emptyConfigs = [
            'LightSaml\SpBundle\LightSamlSpBundle',
        ];

        $bridgeConfig = 'light_saml_symfony_bridge';
        if ('test' === $environment) {
            $bridgeConfig = 'light_saml_symfony_bridge_test';
        }

        $simpleConfigs = [
            'LightSaml\SymfonyBridgeBundle\LightSamlSymfonyBridgeBundle' => $bridgeConfig,
        ];

        if (in_array($bundleClass, $emptyConfigs)) {
            return $config;
        } elseif (isset($simpleConfigs[$bundleClass])) {
            return $config->addContainerResource($this->buildPath($simpleConfigs[$bundleClass]));
        }

        return false;
    }

    private function buildPath($file, $folder = 'suggested')
    {
        return __DIR__."/Resources/config/{$folder}/{$file}.yml";
    }
}

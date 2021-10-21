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

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Claroline\SamlBundle\DependencyInjection\Compiler\SamlConfigPass;
use LightSaml\SpBundle\LightSamlSpBundle;
use LightSaml\SymfonyBridgeBundle\LightSamlSymfonyBridgeBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClarolineSamlBundle extends DistributionPluginBundle
{
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

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $environment = $container->getParameter('kernel.environment');

        $bridgeConfig = 'light_saml_symfony_bridge';
        if ('test' === $environment) {
            $bridgeConfig = 'light_saml_symfony_bridge_test';
        }

        $loader->load($this->getPath()."/Resources/config/suggested/{$bridgeConfig}.yml");
    }
}

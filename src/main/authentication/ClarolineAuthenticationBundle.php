<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AuthenticationBundle;

use Claroline\AuthenticationBundle\DependencyInjection\Compiler\OauthConfigPass;
use Claroline\AuthenticationBundle\Installation\AdditionalInstaller;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use HWI\Bundle\OAuthBundle\HWIOAuthBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineAuthenticationBundle extends DistributionPluginBundle implements ConfigurationProviderInterface
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller($this->getUpdaterServiceLocator());
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OauthConfigPass());
    }

    public function getRequiredThirdPartyBundles(string $environment): array
    {
        return [
            new HWIOAuthBundle(),
        ];
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $config = new ConfigurationBuilder();
        $bundleClass = get_class($bundle);

        $simpleConfigs = [
            'HWI\Bundle\OAuthBundle\HWIOAuthBundle' => 'hwi_oauth',
        ];

        if (isset($simpleConfigs[$bundleClass])) {
            return $config->addContainerResource($this->buildPath($simpleConfigs[$bundleClass]));
        }

        return false;
    }

    private function buildPath($file, $folder = 'suggested')
    {
        return __DIR__."/Resources/config/{$folder}/{$file}.yml";
    }
}

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
use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use HWI\Bundle\OAuthBundle\HWIOAuthBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClarolineAuthenticationBundle extends DistributionPluginBundle
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

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        // simple container configuration, same for every environment
        $simpleConfigs = [
            'hwi_oauth',
        ];

        foreach ($simpleConfigs as $configKey) {
            $loader->load($this->getPath()."/Resources/config/suggested/{$configKey}.yml");
        }
    }
}

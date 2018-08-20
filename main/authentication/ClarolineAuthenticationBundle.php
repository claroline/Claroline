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
use Claroline\AuthenticationBundle\DependencyInjection\Compiler\SsoServerPass;
use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineAuthenticationBundle extends DistributionPluginBundle implements AutoConfigurableInterface, ConfigurationProviderInterface
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SsoServerPass());
        $container->addCompilerPass(new OauthConfigPass());
    }

    public function supports($environment)
    {
        return true;
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();

        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml');
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return 'DataFixtures';
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $config = new ConfigurationBuilder();
        $bundleClass = get_class($bundle);
        $simpleConfigs = [
            'BeSimple\SsoAuthBundle\BeSimpleSsoAuthBundle' => 'sso',
            'HWI\Bundle\OAuthBundle\HWIOAuthBundle' => 'hwi_oauth',
        ];

        if (isset($simpleConfigs[$bundleClass])) {
            return $config->addContainerResource($this->buildPath($simpleConfigs[$bundleClass]));
        }
    }

    private function buildPath($file, $folder = 'suggested')
    {
        return __DIR__."/Resources/config/{$folder}/{$file}.yml";
    }
}

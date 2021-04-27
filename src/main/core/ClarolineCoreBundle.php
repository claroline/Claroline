<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle;

use Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle;
use Claroline\CoreBundle\DependencyInjection\Compiler\DoctrineEntityListenerPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\DynamicConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\MailingConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\PlatformConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\SessionConfigPass;
use Claroline\CoreBundle\Installation\AdditionalInstaller;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle;
use FOS\JsRoutingBundle\FOSJsRoutingBundle;
use Http\HttplugBundle\HttplugBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MakerBundle\MakerBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClarolineCoreBundle extends DistributionPluginBundle implements ConfigurationProviderInterface
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new PlatformConfigPass());
        $container->addCompilerPass(new DynamicConfigPass());
        $container->addCompilerPass(new DoctrineEntityListenerPass());
        $container->addCompilerPass(new MailingConfigPass());
        $container->addCompilerPass(new SessionConfigPass());
    }

    public function supports($environment)
    {
        return in_array($environment, ['prod', 'dev', 'test']);
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();
        $configFile = 'test' === $environment ? 'config_test.yml' : 'config.yml';
        $routingFile = 'test' === $environment ? 'routing_test.yml' : 'routing.yml';

        return $config
            ->addContainerResource($this->getPath()."/Resources/config/app/{$configFile}")
            ->addRoutingResource($this->getPath()."/Resources/config/{$routingFile}");
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $bundleClass = get_class($bundle);
        $config = new ConfigurationBuilder();

        // no special configuration, work in any environment
        $emptyConfigs = [
            'Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle',
            'FOS\JsRoutingBundle\FOSJsRoutingBundle',
            'Claroline\MigrationBundle\ClarolineMigrationBundle',
        ];
        // simple container configuration, same for every environment
        $simpleConfigs = [
            'Symfony\Bundle\TwigBundle\TwigBundle' => 'twig',
            'Http\HttplugBundle\HttplugBundle' => 'httplug',
            'Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle' => 'stof_doctrine_extensions',
            'Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle' => 'sensio_framework_extra',
        ];
        // one configuration file for every standard environment (prod, dev, test)
        $envConfigs = [
            'Symfony\Bundle\FrameworkBundle\FrameworkBundle' => 'framework',
            'Symfony\Bundle\SecurityBundle\SecurityBundle' => 'security',
            'Symfony\Bundle\MonologBundle\MonologBundle' => 'monolog',
            'Doctrine\Bundle\DoctrineBundle\DoctrineBundle' => 'doctrine',
        ];

        if (in_array($bundleClass, $emptyConfigs)) {
            return $config;
        } elseif (isset($simpleConfigs[$bundleClass])) {
            return $config->addContainerResource($this->buildPath($simpleConfigs[$bundleClass]));
        } elseif (isset($envConfigs[$bundleClass])) {
            if (in_array($environment, ['prod', 'dev', 'test'])) {
                return $config->addContainerResource($this->buildPath("{$envConfigs[$bundleClass]}_{$environment}"));
            }
        } elseif ($bundle instanceof BazingaJsTranslationBundle) {
            return $config->addRoutingResource($this->buildPath('bazinga_routing'));
        } elseif (in_array($environment, ['dev', 'test'])) {
            if ($bundle instanceof WebProfilerBundle) {
                return $config
                    ->addContainerResource($this->buildPath('web_profiler'))
                    ->addRoutingResource($this->buildPath('web_profiler_routing'));
            }
        }

        return false;
    }

    public function getRequiredFixturesDirectory(string $environment): string
    {
        return 'DataFixtures/Required';
    }

    public function getPostInstallFixturesDirectory(string $environment): string
    {
        return 'DataFixtures/PostInstall';
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller($this->getUpdaterServiceLocator());
    }

    private function buildPath($file, $folder = 'suggested')
    {
        return $this->getPath()."/Resources/config/{$folder}/{$file}.yml";
    }

    public function getRequiredThirdPartyBundles(string $environment): array
    {
        $bundles = [
            new FrameworkBundle(),
            new SecurityBundle(),
            new MonologBundle(),
            new DoctrineBundle(),
            new DoctrineCacheBundle(),
            new FOSJsRoutingBundle(),
            new TwigBundle(),
            new HttplugBundle(),
            new StofDoctrineExtensionsBundle(),
            new SensioFrameworkExtraBundle(),
            new BazingaJsTranslationBundle(),
        ];

        if (\in_array($environment, ['dev', 'test'], true)) {
            $bundles[] = new WebProfilerBundle();
            $bundles[] = new DebugBundle();
            $bundles[] = new MakerBundle();
        }

        return $bundles;
    }
}

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
use Claroline\CoreBundle\DependencyInjection\Compiler\AnonymousAuthenticationPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\GeoipPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\MailingConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\MessengerConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\PlatformConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\SessionConfigPass;
use Claroline\CoreBundle\Installation\AdditionalInstaller;
use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
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
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ClarolineCoreBundle extends DistributionPluginBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new PlatformConfigPass());
        $container->addCompilerPass(new AnonymousAuthenticationPass());
        $container->addCompilerPass(new MailingConfigPass());
        $container->addCompilerPass(new SessionConfigPass());
        $container->addCompilerPass(new MessengerConfigPass());
        $container->addCompilerPass(new GeoipPass());
    }

    public function supports($environment)
    {
        return in_array($environment, ['prod', 'dev', 'test']);
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

    public function getRequiredThirdPartyBundles(string $environment): array
    {
        $bundles = [
            new FrameworkBundle(),
            new SecurityBundle(),
            new MonologBundle(),
            new DoctrineBundle(),
            new FOSJsRoutingBundle(),
            new TwigBundle(),
            new HttplugBundle(),
            new StofDoctrineExtensionsBundle(),
            new SensioFrameworkExtraBundle(),
            new BazingaJsTranslationBundle(),
        ];

        if (in_array($environment, ['dev', 'test'], true)) {
            $bundles[] = new WebProfilerBundle();
            $bundles[] = new DebugBundle();
            $bundles[] = new MakerBundle();
        }

        return $bundles;
    }

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $environment = $container->getParameter('kernel.environment');

        // simple container configuration, same for every environment
        $simpleConfigs = [
            'twig',
            'httplug',
            'stof_doctrine_extensions',
            'sensio_framework_extra',
        ];

        if (in_array($environment, ['dev', 'test'])) {
            $simpleConfigs[] = 'web_profiler';
        }

        foreach ($simpleConfigs as $configKey) {
            $loader->load($this->buildPath($configKey));
        }

        // one configuration file for every standard environment (prod, dev, test)
        $envConfigs = [
            'framework',
            'security',
            'monolog',
            'doctrine',
        ];

        foreach ($envConfigs as $configKey) {
            $loader->load($this->buildPath("{$configKey}_{$environment}"));
        }
    }

    private function buildPath($file, $folder = 'suggested')
    {
        return $this->getPath()."/Resources/config/{$folder}/{$file}.yml";
    }
}

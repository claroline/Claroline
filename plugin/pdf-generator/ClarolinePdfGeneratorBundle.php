<?php

namespace Claroline\PdfGeneratorBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\BundleBundle\Installation\AdditionalInstaller;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\PdfGeneratorBundle\DependencyInjection\Compiler\DynamicConfigPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Bundle class.
 * Uncomment if necessary.
 */
class ClarolinePdfGeneratorBundle extends PluginBundle implements ConfigurationProviderInterface
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DynamicConfigPass());
    }

    public function getRequiredFixturesDirectory($env)
    {
        return 'DataFixtures';
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $config = new ConfigurationBuilder();
        $bundleClass = get_class($bundle);

        $simpleConfigs = array(
            'Knp\Bundle\SnappyBundle\KnpSnappyBundle' => 'knp_snappy'
        );

        if (isset($simpleConfigs[$bundleClass])) {
            return $config->addContainerResource($this->buildPath($simpleConfigs[$bundleClass]));
        }
    }

    private function buildPath($file, $folder = 'suggested')
    {
        return __DIR__ . "/Resources/config/{$folder}/{$file}.yml";
    }

    /*
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
    */

    public function hasMigrations()
    {
        return false;
    }
}

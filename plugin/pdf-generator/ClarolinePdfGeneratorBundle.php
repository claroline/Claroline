<?php

namespace Claroline\PdfGeneratorBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\PdfGeneratorBundle\DependencyInjection\Compiler\DynamicConfigPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle class.
 * Uncomment if necessary.
 */
class ClarolinePdfGeneratorBundle extends DistributionPluginBundle implements ConfigurationProviderInterface
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

        $simpleConfigs = [
            'Knp\Bundle\SnappyBundle\KnpSnappyBundle' => 'knp_snappy',
        ];

        if (isset($simpleConfigs[$bundleClass])) {
            return $config->addContainerResource($this->buildPath($simpleConfigs[$bundleClass]));
        }
    }

    private function buildPath($file, $folder = 'suggested')
    {
        return __DIR__."/Resources/config/{$folder}/{$file}.yml";
    }

    public function hasMigrations()
    {
        return true;
    }

    public function getExtraRequirements()
    {
        return [
            'wkhtmltopdf' => [
                'test' => function () {
                    return true;
                },
                'failure_msg' => 'wkhtmltopdf not installed',
            ],
        ];
    }
}

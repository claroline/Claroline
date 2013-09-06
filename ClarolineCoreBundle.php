<?php

namespace Claroline\CoreBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\InstallationBundle\Bundle\InstallableBundle;
use Claroline\CoreBundle\Library\Installation\AdditionalInstaller;

class ClarolineCoreBundle extends InstallableBundle implements AutoConfigurableInterface, ConfigurationProviderInterface
{
    public function supports($environment)
    {
        return in_array($environment, array('prod', 'dev', 'test'));
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();
        $configFile = $environment === 'test' ? 'config_test.yml' : 'config.yml';

        return $config
            ->addContainerResource(__DIR__ . "/Resources/config/app/{$configFile}")
            ->addRoutingResource(__DIR__ . '/Resources/config/routing.yml');
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $bundleClass = get_class($bundle);
        $config = new ConfigurationBuilder();

        // no special configuration, work in any environment
        $emptyConfigs = array(
            'Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle',
            'Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle',
            'FOS\JsRoutingBundle\FOSJsRoutingBundle',
            'JMS\AopBundle\JMSAopBundle',
            'JMS\TwigJsBundle\JMSTwigJsBundle',
            'WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle',
            'Zenstruck\Bundle\FormBundle\ZenstruckFormBundle',
            'Bazinga\ExposeTranslationBundle\BazingaExposeTranslationBundle',
            'Claroline\MigrationBundle\ClarolineMigrationBundle',
            'Claroline\Bundle\FrontEndBundle\FrontEndBundle'
        );
        // simple container configuration, same for every environment
        $simpleConfigs = array(
            'Symfony\Bundle\SecurityBundle\SecurityBundle' => 'security',
            'Symfony\Bundle\TwigBundle\TwigBundle' => 'twig',
            'Symfony\Bundle\AsseticBundle\AsseticBundle' => 'assetic',
            'JMS\DiExtraBundle\JMSDiExtraBundle' => 'jms_di_extra',
            'JMS\SecurityExtraBundle\JMSSecurityExtraBundle' => 'jms_security_extra',
            'Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle' => 'stof_doctrine_extensions',
            'BeSimple\SsoAuthBundle\BeSimpleSsoAuthBundle' => 'sso',
            'Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle' => 'stfalcon_tinymce',
            'IDCI\Bundle\ExporterBundle\IDCIExporterBundle' => 'idci_exporter',
            'ICAPLyon1\Bundle\SimpleTagBundle\ICAPLyon1SimpleTagBundle' => 'icap_simple_tag'
        );
        // one configuration file for every standard environment (prod, dev, test)
        $envConfigs = array(
            'Symfony\Bundle\FrameworkBundle\FrameworkBundle' => 'framework',
            'Symfony\Bundle\MonologBundle\MonologBundle' => 'monolog',
            'Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle' => 'swiftmailer',
            'Doctrine\Bundle\DoctrineBundle\DoctrineBundle' => 'doctrine'
        );

        if (in_array($bundleClass, $emptyConfigs)) {
            return $config;
        } elseif (isset($simpleConfigs[$bundleClass])) {
            return $config->addContainerResource($this->buildPath($simpleConfigs[$bundleClass]));
        } elseif (isset($envConfigs[$bundleClass])) {
            if (in_array($environment, array('prod', 'dev', 'test'))) {
                return $config->addContainerResource($this->buildPath("{$envConfigs[$bundleClass]}_{$environment}"));
            }
        } elseif ($bundle instanceof \Claroline\BadgeBundle\ClarolineBadgeBundle) {
            return $config->addRoutingResource($this->buildPath('badge_routing'));
        } elseif (in_array($environment, array('dev', 'test'))) {
            if ($bundle instanceof \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle) {
                return $config
                    ->addContainerResource($this->buildPath('web_profiler'))
                    ->addRoutingResource($this->buildPath('web_profiler_routing'));
            } elseif ($bundle instanceof \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle) {
                return $config;
            }
        }

        return false;
    }

    public function getRequiredFixturesDirectory($environment)
    {
        return $environment !== 'test' ? 'DataFixtures/Required' : null;
    }

    public function getOptionalFixturesDirectory($environment)
    {
        return $environment !== 'test' ? 'DataFixtures/Demo' : null;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    private function buildPath($file, $folder = 'suggested')
    {
        return __DIR__ . "/Resources/config/{$folder}/{$file}.yml";
    }
}

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

use Claroline\CoreBundle\DependencyInjection\Compiler\DoctrineEntityListenerPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\DynamicConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\ImportersConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\RichTextFormatterConfigPass;
use Claroline\CoreBundle\DependencyInjection\Compiler\RuleConstraintsConfigPass;
use FOS\OAuthServerBundle\FOSOAuthServerBundle;
use IDCI\Bundle\ExporterBundle\IDCIExporterBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\InstallationBundle\Bundle\InstallableBundle;
use Claroline\CoreBundle\Library\Installation\AdditionalInstaller;
use Zenstruck\Bundle\FormBundle\ZenstruckFormBundle;

class ClarolineCoreBundle extends InstallableBundle implements AutoConfigurableInterface, ConfigurationProviderInterface
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DynamicConfigPass());
        $container->addCompilerPass(new ImportersConfigPass());
        $container->addCompilerPass(new RichTextFormatterConfigPass());
        $container->addCompilerPass(new DoctrineEntityListenerPass());
        $container->addCompilerPass(new RuleConstraintsConfigPass());
    }

    public function supports($environment)
    {
        return in_array($environment, array('prod', 'dev', 'test'));
    }

    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();
        $configFile = $environment === 'test' ? 'config_test.yml' : 'config.yml';
        $routingFile = $environment === 'test' ? 'routing_test.yml' : 'routing.yml';

        return $config
            ->addContainerResource(__DIR__ . "/Resources/config/app/{$configFile}")
            ->addRoutingResource(__DIR__ . "/Resources/config/{$routingFile}");
    }


    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $bundleClass = get_class($bundle);
        $config = new ConfigurationBuilder();

        // no special configuration, work in any environment
        $emptyConfigs = array(
            'Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle',
            'FOS\JsRoutingBundle\FOSJsRoutingBundle',
            'JMS\AopBundle\JMSAopBundle',
            'JMS\TwigJsBundle\JMSTwigJsBundle',
            'WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle',
            'Claroline\MigrationBundle\ClarolineMigrationBundle',
            'Claroline\Bundle\FrontEndBundle\FrontEndBundle',
            'JMS\SerializerBundle\JMSSerializerBundle',
        );
        // simple container configuration, same for every environment
        $simpleConfigs = array(
            'Symfony\Bundle\SecurityBundle\SecurityBundle'                  => 'security',
            'Symfony\Bundle\TwigBundle\TwigBundle'                          => 'twig',
            'Symfony\Bundle\AsseticBundle\AsseticBundle'                    => 'assetic',
            'JMS\DiExtraBundle\JMSDiExtraBundle'                            => 'jms_di_extra',
            'JMS\SecurityExtraBundle\JMSSecurityExtraBundle'                => 'jms_security_extra',
            'Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle'    => 'stof_doctrine_extensions',
            'BeSimple\SsoAuthBundle\BeSimpleSsoAuthBundle'                  => 'sso',
            'Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle'           => 'stfalcon_tinymce',
            'Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle' => 'sensio_framework_extra',
            'FOS\RestBundle\FOSRestBundle'                                  => 'fos_rest',
            'Gregwar\CaptchaBundle\GregwarCaptchaBundle'                    => 'gregwar_captcha',
            'Knp\Bundle\MenuBundle\KnpMenuBundle'                           => 'knp_menu'
        );
        // one configuration file for every standard environment (prod, dev, test)
        $envConfigs = array(
            'Symfony\Bundle\FrameworkBundle\FrameworkBundle'     => 'framework',
            'Symfony\Bundle\MonologBundle\MonologBundle'         => 'monolog',
            'Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle' => 'swiftmailer',
            'Doctrine\Bundle\DoctrineBundle\DoctrineBundle'      => 'doctrine'
        );

        if (in_array($bundleClass, $emptyConfigs)) {
            return $config;
        } elseif (isset($simpleConfigs[$bundleClass])) {
            return $config->addContainerResource($this->buildPath($simpleConfigs[$bundleClass]));
        } elseif (isset($envConfigs[$bundleClass])) {
            if (in_array($environment, array('prod', 'dev', 'test'))) {
                return $config->addContainerResource($this->buildPath("{$envConfigs[$bundleClass]}_{$environment}"));
            }
        } elseif ($bundle instanceof \Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle) {
            return $config->addRoutingResource($this->buildPath('bazinga_routing'));
        } elseif ($bundle instanceof FOSOAuthServerBundle) {
            $config = new ConfigurationBuilder();
            $config
                ->addContainerResource($this->buildPath('fos_oauth_server_config'))
                ->addRoutingResource($this->buildPath('fos_oauth_server_routing'));

            return $config;
        } elseif ($bundle instanceof NelmioApiDocBundle) {
            $config = new ConfigurationBuilder();
            $config
                ->addContainerResource($this->buildPath('nelmio_api_doc_config'))
                ->addRoutingResource($this->buildPath('nelmio_api_doc_routing'));

            return $config;
        } elseif ($bundle instanceof IDCIExporterBundle) {
            $config = new ConfigurationBuilder();
            $config
                ->addContainerResource($this->buildPath('idci_exporter'))
                ->addRoutingResource($this->buildPath('idci_exporter_routing'));

            return $config;
        } elseif ($bundle instanceof ZenstruckFormBundle) {
            $config = new ConfigurationBuilder();
            $config
                ->addContainerResource($this->buildPath('zenstruck_form'))
                ->addRoutingResource($this->buildPath('zenstruck_form_routing'));

            return $config;
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
        return $environment !== 'test' ? 'DataFixtures/Demo/Main' : null;
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

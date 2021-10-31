<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AppBundle;

use Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle;
use Claroline\AppBundle\DependencyInjection\Compiler\ApiConfigPass;
use Claroline\AppBundle\DependencyInjection\Compiler\RouterPass;
use Claroline\KernelBundle\Bundle\AutoConfigurableInterface;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use FOS\JsRoutingBundle\FOSJsRoutingBundle;
use Http\HttplugBundle\HttplugBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\RouteCollectionBuilder;

class ClarolineAppBundle extends Bundle implements AutoConfigurableInterface
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ApiConfigPass());
        $container->addCompilerPass(new RouterPass());
    }

    public function supports(string $environment): bool
    {
        return true;
    }

    public function getRequiredBundles(string $environment): array
    {
        return [
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
    }

    public function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $environment = $container->getParameter('kernel.environment');

        $configs = [
            // simple container configuration, same for every environment
            'twig' => false,
            'httplug' => false,
            'stof_doctrine_extensions' => false,
            'sensio_framework_extra' => false,

            // one configuration file for every standard environment (prod, dev, test)
            'framework' => true,
            'security' => true,
            'monolog' => true,
            'doctrine' => true,
        ];

        foreach ($configs as $configKey => $envConfig) {
            if ($envConfig) {
                $loader->load($this->getPath()."/Resources/config/suggested/{$configKey}_{$environment}.yml");
            } else {
                $loader->load($this->getPath()."/Resources/config/suggested/{$configKey}.yml");
            }
        }
    }

    public function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import($this->getPath().'/Resources/config/routing.yml');
    }
}

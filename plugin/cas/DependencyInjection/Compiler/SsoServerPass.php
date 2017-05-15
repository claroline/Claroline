<?php

/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 3/6/17
 */

namespace Claroline\CasBundle\DependencyInjection\Compiler;

use BeSimple\SsoAuthBundle\DependencyInjection\Compiler\FactoryPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SsoServerPass extends FactoryPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $this->setFactoryAndServerClasses($container);
        parent::process($container);
        $this->setCasServerConfig($container);
    }

    private function setCasServerConfig(ContainerBuilder $container)
    {
        $parameter = 'be_simple.sso_auth.manager.cas_sso';
        if (!$container->hasParameter($parameter)) {
            return;
        }
        $factoryBuilder = $container->getDefinition('claroline.factory.cas_configuration');
        $factoryBuilder->addMethodCall('updateCasServerConfiguration', []);
    }

    private function setFactoryAndServerClasses(ContainerBuilder $container)
    {
        if ($container->has('be_simple.sso_auth.client')) {
            // Change dynamically the ssl version for BeSimpleSsoBundle
            $container
                ->getDefinition('be_simple.sso_auth.client')
                ->setClass('Claroline\CasBundle\Library\Sso\CasAdaptiveClient')
                ->addArgument($container->getParameter('be_simple.sso_auth.client.option.curlopt_sslversion.key'))
                ->addArgument(new Reference('claroline.config.platform_config_handler'));
        }
        if ($container->has('be_simple.sso_auth.server.cas')) {
            $container
                ->getDefinition('be_simple.sso_auth.server.cas')
                ->setClass('Claroline\CasBundle\Library\Sso\CasServer');
        }
        if ($container->has('be_simple.sso_auth.factory')) {
            $container
                ->getDefinition('be_simple.sso_auth.factory')
                ->setClass('Claroline\CasBundle\Library\Sso\CasFactory');
        }
    }
}

<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineEntityListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $config = $container->getDefinition('doctrine.orm.default_configuration');
        $config->addMethodCall(
            'setEntityListenerResolver',
            [new Reference('Claroline\CoreBundle\Doctrine\EntityListenerResolver')]
        );

        $definition = $container->getDefinition('Claroline\CoreBundle\Doctrine\EntityListenerResolver');
        $services = $container->findTaggedServiceIds('doctrine.entity_listener');

        foreach (array_keys($services) as $service) {
            $definition->addMethodCall(
                'addMapping',
                [$container->getDefinition($service)->getClass(), $service]
            );
        }
    }
}

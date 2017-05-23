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

class ApiConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //finder pass
        if (false === $container->hasDefinition('claroline.API.finder')) {
            return;
        }

        $finder = $container->getDefinition('claroline.API.finder');

        foreach ($container->findTaggedServiceIds('claroline.finder') as $id => $attributes) {
            $finder->addMethodCall('addFinder', [new Reference($id)]);
        }

        //serializer pass
        if (false === $container->hasDefinition('claroline.API.serializer')) {
            return;
        }

        $serializer = $container->getDefinition('claroline.API.serializer');

        foreach ($container->findTaggedServiceIds('claroline.serializer') as $id => $attributes) {
            $serializer->addMethodCall('addSerializer', [new Reference($id)]);
        }
    }
}

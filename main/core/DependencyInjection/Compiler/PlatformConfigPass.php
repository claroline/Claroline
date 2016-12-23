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

class PlatformConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('claroline.config.platform_config_handler')) {
            return;
        }

        $configHandler = $container->getDefinition('claroline.config.platform_config_handler');

        foreach (array_keys($container->findTaggedServiceIds('claroline.configuration')) as $id) {
            $configHandler->addMethodCall('addDefaultParameters', [new Reference($id)]);
        }
    }
}

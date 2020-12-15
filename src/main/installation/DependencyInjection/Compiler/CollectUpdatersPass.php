<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\InstallationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collects all "claroline.platform.updater" tagged services and make them available through
 * the "claroline.updater_locator" service locator.
 */
class CollectUpdatersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $updaterRefs = [];
        foreach (array_keys($container->findTaggedServiceIds('claroline.platform.updater')) as $id) {
            $updaterRefs[$id] = new Reference($id);
        }

        if (!$updaterRefs) {
            return;
        }

        $locatorId = ServiceLocatorTagPass::register($container, $updaterRefs);
        $container->setAlias('claroline.updater_locator', (string) $locatorId)->setPublic(true);
    }
}

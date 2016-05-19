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

class RichTextFormatterConfigPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('claroline.importer.rich_text_formatter')) {
            return;
        }

        $transferManager = $container->getDefinition('claroline.importer.rich_text_formatter');

        foreach ($container->findTaggedServiceIds('claroline.importer') as $id => $attributes) {
            $transferManager->addMethodCall('addImporter', array(new Reference($id)));
        }
    }
}

<?php

namespace UJM\ExoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ItemDefinitionsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ujm_exo.collection.item_definitions')) {
            return;
        }

        // Get the serializer collector
        $definition = $container->findDefinition('ujm_exo.collection.item_definitions');

        // Get all defined question serializers
        $taggedServices = $container->findTaggedServiceIds('ujm_exo.definition.item');

        $serviceIds = array_keys($taggedServices);
        foreach ($serviceIds as $id) {
            $definition->addMethodCall('addDefinition', [new Reference($id)]);
        }

        // Get all defined content item serializers
        $taggedContentItemServices = $container->findTaggedServiceIds('ujm_exo.definition.content_item');
        $contentItemServiceIds = array_keys($taggedContentItemServices);

        foreach ($contentItemServiceIds as $id) {
            $definition->addMethodCall('addContentItemDefinition', [new Reference($id)]);
        }
    }
}

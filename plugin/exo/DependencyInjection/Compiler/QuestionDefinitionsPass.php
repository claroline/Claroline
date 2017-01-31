<?php

namespace UJM\ExoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class QuestionDefinitionsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ujm_exo.collection.question_definitions')) {
            return;
        }

        // Get the serializer collector
        $definition = $container->findDefinition('ujm_exo.collection.question_definitions');

        // Get all defined question serializers
        $taggedServices = $container->findTaggedServiceIds('ujm_exo.definition.question');

        $serviceIds = array_keys($taggedServices);
        foreach ($serviceIds as $id) {
            $definition->addMethodCall('addDefinition', [new Reference($id)]);
        }
    }
}

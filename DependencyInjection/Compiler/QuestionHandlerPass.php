<?php

namespace UJM\ExoBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class QuestionHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ujm.exo.question_handler_collector')) {
            return;
        }

        $definition = $container->findDefinition('ujm.exo.question_handler_collector');
        $taggedServices = $container->findTaggedServiceIds('ujm.exo.question_handler');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addHandler', [new Reference($id)]);
        }
    }
}

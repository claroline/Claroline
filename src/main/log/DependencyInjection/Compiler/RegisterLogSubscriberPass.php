<?php

namespace Claroline\LogBundle\DependencyInjection\Compiler;

use Claroline\LogBundle\Component\Log\LogProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register service tagged with "claroline.component.log" as EventSubscriber.
 */
final class RegisterLogSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('event_dispatcher') || !$container->has('claroline.log_provider')) {
            return;
        }

        $eventDispatcherDefinition = $container->findDefinition('event_dispatcher');

        // Get all defined logs
        $taggedServices = $container->findTaggedServiceIds(LogProvider::getServiceTag());
        $taggedServiceIds = array_keys($taggedServices);
        foreach ($taggedServiceIds as $id) {
            // register logs as event subscriber
            $eventDispatcherDefinition->addMethodCall('addSubscriber', [new Reference($id)]);
        }
    }
}

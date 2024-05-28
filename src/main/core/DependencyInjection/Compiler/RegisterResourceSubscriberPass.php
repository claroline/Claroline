<?php

namespace Claroline\CoreBundle\DependencyInjection\Compiler;

use Claroline\CoreBundle\Component\Resource\ResourceProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register service tagged with "claroline.component.resource" as EventSubscriber.
 */
final class RegisterResourceSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('event_dispatcher') || !$container->has('claroline.resource_provider')) {
            return;
        }

        $eventDispatcherDefinition = $container->findDefinition('event_dispatcher');

        // get all defined resources
        $taggedServices = $container->findTaggedServiceIds(ResourceProvider::getServiceTag());
        $taggedServiceIds = array_keys($taggedServices);
        foreach ($taggedServiceIds as $id) {
            // register resources as event subscriber
            $eventDispatcherDefinition->addMethodCall('addSubscriber', [new Reference($id)]);
        }
    }
}

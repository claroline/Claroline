<?php

namespace Claroline\NotificationBundle\DependencyInjection\Compiler;

use Claroline\NotificationBundle\Component\Notification\NotificationProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register service tagged with "claroline.component.notification" as EventSubscriber.
 */
final class RegisterNotificationSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('event_dispatcher') || !$container->has('claroline.notification_provider')) {
            return;
        }

        $eventDispatcherDefinition = $container->findDefinition('event_dispatcher');

        // Get all defined notifications
        $taggedServices = $container->findTaggedServiceIds(NotificationProvider::getServiceTag());
        $taggedServiceIds = array_keys($taggedServices);
        foreach ($taggedServiceIds as $id) {
            // register notifications as event subscriber
            $eventDispatcherDefinition->addMethodCall('addSubscriber', [new Reference($id)]);
        }
    }
}

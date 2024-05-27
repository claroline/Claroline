<?php

namespace Claroline\NotificationBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Claroline\NotificationBundle\DependencyInjection\Compiler\RegisterNotificationSubscriberPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ClarolineNotificationBundle extends DistributionPluginBundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterNotificationSubscriberPass());
    }
}

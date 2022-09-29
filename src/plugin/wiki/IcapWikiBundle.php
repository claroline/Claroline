<?php

namespace Icap\WikiBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Icap\NotificationBundle\IcapNotificationBundle;

class IcapWikiBundle extends DistributionPluginBundle
{
    public function getRequiredPlugins(): array
    {
        return [
            IcapNotificationBundle::class,
        ];
    }
}

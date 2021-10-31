<?php

namespace Icap\BlogBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Icap\NotificationBundle\IcapNotificationBundle;

class IcapBlogBundle extends DistributionPluginBundle
{
    public function getRequiredPlugins()
    {
        return [
            IcapNotificationBundle::class,
        ];
    }
}

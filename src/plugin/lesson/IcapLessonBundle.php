<?php

namespace Icap\LessonBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Icap\NotificationBundle\IcapNotificationBundle;

class IcapLessonBundle extends DistributionPluginBundle
{
    public function getRequiredPlugins()
    {
        return [
            IcapNotificationBundle::class,
        ];
    }
}

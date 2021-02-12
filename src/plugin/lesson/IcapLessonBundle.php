<?php

namespace Icap\LessonBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Icap\LessonBundle\Installation\AdditionalInstaller;

class IcapLessonBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getRequiredPlugins()
    {
        return ['Icap\\NotificationBundle\\IcapNotificationBundle'];
    }
}

<?php

namespace Icap\BlogBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Icap\BlogBundle\Installation\AdditionalInstaller;

class IcapBlogBundle extends DistributionPluginBundle
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

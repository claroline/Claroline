<?php

namespace Icap\WikiBundle;

use Claroline\KernelBundle\Bundle\DistributionPluginBundle;
use Icap\WikiBundle\Installation\AdditionalInstaller;

class IcapWikiBundle extends DistributionPluginBundle
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

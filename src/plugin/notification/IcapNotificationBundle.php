<?php

namespace Icap\NotificationBundle;

use Claroline\CoreBundle\Library\DistributionPluginBundle;
use Icap\NotificationBundle\Installation\AdditionalInstaller;

class IcapNotificationBundle extends DistributionPluginBundle
{
    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }
}

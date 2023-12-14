<?php

namespace Icap\NotificationBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class IcapNotificationInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

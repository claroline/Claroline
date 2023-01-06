<?php

namespace Icap\NotificationBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use Icap\NotificationBundle\Installation\Updater\Updater130700;

class IcapNotificationInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.7.0' => Updater130700::class,
        ];
    }
}

<?php

namespace Claroline\AuthenticationBundle\Installation;

use Claroline\AuthenticationBundle\Installation\Updater\Updater130002;
use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineAuthenticationInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.0.2' => Updater130002::class,
        ];
    }
}

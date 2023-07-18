<?php

namespace Claroline\AuthenticationBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use Claroline\AuthenticationBundle\Installation\Updater\Updater140000;

class ClarolineAuthenticationInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '14.0.0' => Updater140000::class,
        ];
    }
}

<?php

namespace Claroline\AuthenticationBundle\Installation;

use Claroline\AuthenticationBundle\Installation\Updater\Updater140000;
use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineAuthenticationInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '14.0.0' => Updater140000::class,
        ];
    }

    public function hasMigrations(): bool
    {
        return true;
    }
}

<?php

namespace Claroline\ThemeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use Claroline\ThemeBundle\Installation\Updater\Updater130600;

class ClarolineThemeInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.6.0' => Updater130600::class,
        ];
    }
}

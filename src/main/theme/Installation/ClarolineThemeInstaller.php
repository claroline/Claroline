<?php

namespace Claroline\ThemeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineThemeInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

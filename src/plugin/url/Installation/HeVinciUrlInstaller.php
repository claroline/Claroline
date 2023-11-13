<?php

namespace HeVinci\UrlBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class HeVinciUrlInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

<?php

namespace Claroline\PrivacyBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolinePrivacyInstaller extends AdditionalInstaller
{
    public function hasFixtures(): bool
    {
        return true;
    }

    public function hasMigrations(): bool
    {
        return true;
    }
}

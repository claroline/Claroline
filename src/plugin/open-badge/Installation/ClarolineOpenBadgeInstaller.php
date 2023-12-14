<?php

namespace Claroline\OpenBadgeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineOpenBadgeInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }

    public function hasFixtures(): bool
    {
        return true;
    }
}

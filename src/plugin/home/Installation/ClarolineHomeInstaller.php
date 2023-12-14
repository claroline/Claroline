<?php

namespace Claroline\HomeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineHomeInstaller extends AdditionalInstaller
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

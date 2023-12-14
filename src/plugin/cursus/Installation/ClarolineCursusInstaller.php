<?php

namespace Claroline\CursusBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineCursusInstaller extends AdditionalInstaller
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

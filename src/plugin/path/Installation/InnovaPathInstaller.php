<?php

namespace Innova\PathBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class InnovaPathInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

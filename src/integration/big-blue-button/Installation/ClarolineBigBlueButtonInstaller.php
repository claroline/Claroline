<?php

namespace Claroline\BigBlueButtonBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineBigBlueButtonInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

<?php

namespace Claroline\TagBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineTagInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

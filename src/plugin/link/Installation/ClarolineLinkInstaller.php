<?php

namespace Claroline\LinkBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineLinkInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

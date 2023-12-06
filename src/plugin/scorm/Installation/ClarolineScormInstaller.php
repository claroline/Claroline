<?php

namespace Claroline\ScormBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineScormInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

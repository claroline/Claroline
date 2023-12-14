<?php

namespace Claroline\MessageBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineMessageInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

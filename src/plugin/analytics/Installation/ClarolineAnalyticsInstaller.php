<?php

namespace Claroline\AnalyticsBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineAnalyticsInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return false;
    }
}

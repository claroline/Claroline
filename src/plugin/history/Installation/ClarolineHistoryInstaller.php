<?php

namespace Claroline\HistoryBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineHistoryInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

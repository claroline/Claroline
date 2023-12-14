<?php

namespace Claroline\RssBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineRssInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

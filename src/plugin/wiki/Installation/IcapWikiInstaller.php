<?php

namespace Icap\WikiBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class IcapWikiInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

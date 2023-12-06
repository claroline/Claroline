<?php

namespace Icap\BibliographyBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class IcapBibliographyInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

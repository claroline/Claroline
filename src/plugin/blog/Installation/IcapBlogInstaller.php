<?php

namespace Icap\BlogBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class IcapBlogInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

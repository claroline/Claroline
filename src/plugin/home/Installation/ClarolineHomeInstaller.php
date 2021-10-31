<?php

namespace Claroline\HomeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineHomeInstaller extends AdditionalInstaller
{
    public function getPostInstallFixturesDirectory(): string
    {
        return 'Installation/DataFixtures/PostInstall';
    }
}

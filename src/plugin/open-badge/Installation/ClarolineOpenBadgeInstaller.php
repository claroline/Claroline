<?php

namespace Claroline\OpenBadgeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineOpenBadgeInstaller extends AdditionalInstaller
{
    public function getRequiredFixturesDirectory(): string
    {
        return 'Installation/DataFixtures/Required';
    }
}

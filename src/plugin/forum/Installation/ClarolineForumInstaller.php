<?php

namespace Claroline\ForumBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineForumInstaller extends AdditionalInstaller
{
    public function getRequiredFixturesDirectory(): string
    {
        return 'Installation/DataFixtures/Required';
    }
}

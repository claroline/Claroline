<?php

namespace Claroline\ForumBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineForumInstaller extends AdditionalInstaller
{
    public function hasFixtures(): bool
    {
        return true;
    }
}

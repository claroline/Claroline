<?php

namespace Claroline\PeerTubeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolinePeerTubeInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

<?php

namespace Claroline\VideoPlayerBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineVideoPlayerInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return false;
    }
}

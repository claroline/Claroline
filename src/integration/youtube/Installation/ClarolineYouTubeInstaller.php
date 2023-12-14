<?php

namespace Claroline\YouTubeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineYouTubeInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

<?php

namespace Claroline\AudioPlayerBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineAudioPlayerInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }
}

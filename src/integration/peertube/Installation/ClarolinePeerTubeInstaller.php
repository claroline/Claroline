<?php

namespace Claroline\PeerTubeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use Claroline\PeerTubeBundle\Installation\Updater\Updater140103;

class ClarolinePeerTubeInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }

    public static function getUpdaters(): array
    {
        return [
            '14.1.3' => Updater140103::class,
        ];
    }
}

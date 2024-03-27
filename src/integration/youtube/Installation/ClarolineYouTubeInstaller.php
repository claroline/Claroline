<?php

namespace Claroline\YouTubeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use Claroline\YouTubeBundle\Installation\Updater\Updater140103;

class ClarolineYouTubeInstaller extends AdditionalInstaller
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

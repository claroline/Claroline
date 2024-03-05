<?php

namespace Claroline\YouTubeBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use Claroline\YouTubeBundle\Installation\Updater\Updater141000;

class ClarolineYouTubeInstaller extends AdditionalInstaller
{
    public function hasMigrations(): bool
    {
        return true;
    }

    public static function getUpdaters(): array
    {
        return [
            '14.1.0' => Updater141000::class,
        ];
    }
}

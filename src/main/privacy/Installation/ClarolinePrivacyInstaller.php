<?php

namespace Claroline\PrivacyBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use Claroline\PrivacyBundle\Installation\Updater\Updater141000;
use Claroline\PrivacyBundle\Installation\Updater\Updater142000;

class ClarolinePrivacyInstaller extends AdditionalInstaller
{
    public function hasFixtures(): bool
    {
        return true;
    }

    public function hasMigrations(): bool
    {
        return true;
    }

    public static function getUpdaters(): array
    {
        return [
            '14.1.0' => Updater141000::class,
            '14.2.0' => Updater142000::class,
        ];
    }
}

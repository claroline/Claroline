<?php

namespace Claroline\PrivacyBundle\Installation;

use Claroline\PrivacyBundle\Installation\Updater\Updater140000;
use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolinePrivacyInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '14.0.0' => Updater140000::class,
        ];
    }

    public function getFixturesDirectory(): string
    {
        return 'Installation/DataFixtures';
    }
}

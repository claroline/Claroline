<?php

namespace Claroline\AgendaBundle\Installation;

use Claroline\AgendaBundle\Installation\Updater\Updater130013;
use Claroline\InstallationBundle\Additional\AdditionalInstaller;

class ClarolineAgendaInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.0.13' => Updater130013::class,
        ];
    }

    public function getFixturesDirectory(): string
    {
        return 'Installation/DataFixtures';
    }
}

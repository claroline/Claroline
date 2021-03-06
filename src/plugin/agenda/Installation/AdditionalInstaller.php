<?php

namespace Claroline\AgendaBundle\Installation;

use Claroline\AgendaBundle\Installation\Updater\Updater130013;
use Claroline\AgendaBundle\Installation\Updater\Updater130018;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.0.13' => Updater130013::class,
            '13.0.18' => Updater130018::class,
        ];
    }
}

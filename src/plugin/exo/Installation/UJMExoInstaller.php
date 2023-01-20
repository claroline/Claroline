<?php

namespace UJM\ExoBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use UJM\ExoBundle\Installation\Updater\Updater130700;

class UJMExoInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.7.0' => Updater130700::class,
        ];
    }
}

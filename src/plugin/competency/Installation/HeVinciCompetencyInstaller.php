<?php

namespace HeVinci\CompetencyBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller;
use HeVinci\CompetencyBundle\Installation\Updater\Updater130600;
use HeVinci\CompetencyBundle\Installation\Updater\Updater130700;

class HeVinciCompetencyInstaller extends AdditionalInstaller
{
    public static function getUpdaters(): array
    {
        return [
            '13.6.0' => Updater130600::class,
            '13.7.0' => Updater130700::class,
        ];
    }
}
